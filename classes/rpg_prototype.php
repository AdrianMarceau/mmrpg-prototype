<?php
/**
 * Mega Man RPG Prototype
 * <p>The global prototype for the Mega Man RPG Prototype.</p>
 */
class rpg_prototype {

    /**
     * Create a new RPG prototype object.
     * This is a wrapper class for static functions,
     * so object initialization is not necessary.
     */
    public function rpg_prototype(){ }

    // Define a function for calculating required experience points to the next level
    public static function calculate_experience_required($this_level, $max_level = 100, $min_experience = 1000){

        $last_level = $this_level - 1;
        $level_mod = $this_level / $max_level;
        $this_experience = round($min_experience + ($last_level * $level_mod * $min_experience));

        return $this_experience;
    }

    // Define a function for calculating required experience points to the next level
    public static function calculate_level_by_experience($this_experience, $max_level = 100, $min_experience = 1000){
        $temp_total_experience = 0;
        for ($this_level = 1; $this_level < $max_level; $this_level++){
            $temp_experience = rpg_prototype::calculate_experience_required($this_level, $max_level, $min_experience);
            $temp_total_experience += $temp_experience;
            if ($temp_total_experience > $this_experience){
                return $this_level - 1;
            }
        }
        return $max_level;
    }

    // Define a function for checking a player has completed the prototype
    public static function campaign_complete($player_token = ''){
        // Pull in global variables
        //global $mmrpg_index;
        $mmrpg_index_players = rpg_player::get_index();
        $session_token = rpg_game::session_token();
        // If the player token was provided, do a quick check
        if (!empty($player_token)){
            // Return the prototype complete flag for this player
            if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){ return 1; }
            else { return 0; }
        }
        // Otherwise loop through all players and check each
        else {
            // Loop through unlocked robots and return true if any are found to be completed
            $complete_count = 0;
            foreach ($mmrpg_index_players AS $player_token => $player_info){
                if (rpg_game::player_unlocked($player_token)){
                    if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){
                        $complete_count += 1;
                    }
                }
            }
            // Otherwise return false by default
            return $complete_count;
        }
    }

    // Define a function for checking the battle's prototype points total
    public static function event_complete($event_token){
        // Return the current point total for thisgame
        $session_token = rpg_game::session_token();
        if (!empty($_SESSION[$session_token]['flags']['events'][$event_token])){ return 1; }
        else { return 0; }
    }

    // Define a function for checking if a prototype battle has been completed
    public static function battle_complete($player_token, $battle_token){
        // Check if this battle has been completed and return true is it was
        $session_token = rpg_game::session_token();
        if (!empty($player_token)){
            return isset($_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token] : false;
        } elseif (!empty($_SESSION[$session_token]['values']['battle_complete'])){
            foreach ($_SESSION[$session_token]['values']['battle_complete'] AS $player_token => $player_batles){
                if (isset($player_batles[$battle_token])){ return $player_batles[$battle_token]; }
                else { continue; }
            }
            return false;
        } else {
            return false;
        }
    }
    // Define a function for checking if a prototype battle has been failured
    public static function battle_failure($player_token, $battle_token){
        // Check if this battle has been failured and return true is it was
        $session_token = rpg_game::session_token();
        return isset($_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token] : false;
    }

    // Define a function for counting the number of completed prototype battles
    public static function battles_complete($player_token = '', $unique = true){
        // Define the game session helper var
        $session_token = rpg_game::session_token();
        // Collect the battle complete count from the session if set
        if (!empty($player_token)){
            $temp_battles_complete = isset($_SESSION[$session_token]['values']['battle_complete'][$player_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token] : array();
        } else {
            $temp_battles_complete = array();
            if (isset($_SESSION[$session_token]['values']['battle_complete'])){
                foreach ($_SESSION[$session_token]['values']['battle_complete'] AS $player_token => $battle_array){
                    $temp_battles_complete = array_merge($temp_battles_complete, $battle_array);
                }
            }
            $player_token = '';
        }
        //if (empty($player_token)){ die('$player_token = '.$player_token.', $unique = '.($unique ? 1 : 0).',  $count = '.count($temp_battles_complete).'<br />'.print_r($temp_battles_complete, true)); }
        // Check if only unique battles were requested or ALL battles
        if ($unique == true){
         $temp_count = count($temp_battles_complete);
         return $temp_count;
        } else {
         $temp_count = 0;
         foreach ($temp_battles_complete AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
         return $temp_count;
        }
    }

    // Define a function for counting the number of failured prototype battles
    public static function battles_failure($player_token, $unique = true){
        // Define the game session helper var
        $session_token = rpg_game::session_token();
        // Collect the battle failure count from the session if set
        $temp_battle_failures = isset($_SESSION[$session_token]['values']['battle_failure'][$player_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token] : array();
        // Check if only unique battles were requested or ALL battles
        if (!empty($unique)){
         $temp_count = count($temp_battle_failures);
         return $temp_count;
        } else {
         $temp_count = 0;
         foreach ($temp_battle_failures AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
         return $temp_count;
        }
    }

    // Define a function for displaying prototype battle option markup
    public static function options_markup(&$battle_options, $player_token){
        // Refence the global config and index objects for easy access
        global $mmrpg_index, $db;
        $mmrpg_index_fields = rpg_field::get_index();

        // Define the variable to collect option markup
        $this_markup = '';

        // Count the number of completed battle options for this group and update the variable
        foreach ($battle_options AS $this_key => $this_info){
            // Define the chapter if not set
            if (!isset($this_info['option_chapter'])){ $this_info['option_chapter'] = '0'; }
            // If this is an event message type option, simply display the text/images
            if (!empty($this_info['option_type']) && $this_info['option_type'] == 'message'){
                // Generate the option markup for the event message
                $temp_optiontitle = $this_info['option_maintext'];
                $temp_optionimages = !empty($this_info['option_images']) ? $this_info['option_images'] : '';
                $temp_optiontext = '<span class="multi"><span class="maintext">'.$this_info['option_maintext'].'</span></span>';
                $this_markup .= '<a data-chapter="'.$this_info['option_chapter'].'" class="option option_message option_1x4 option_this-'.$player_token.'-message" style="'.(!empty($this_info['option_style']) ? $this_info['option_style'] : '').'"><div class="chrome"><div class="inset"><label class="'.(!empty($temp_optionimages) ? 'has_image' : '').'">'.$temp_optionimages.$temp_optiontext.'</label></div></div></a>'."\n";
            }
            // Otherwise, if this is a normal battle option
            else {
                // If the skip flag is set, continue to the next index
                //if (isset($this_info['flag_skip']) && $this_info['flag_skip'] == true){ continue; }
                // Collect the current battle and field info from the index
                if (!isset($this_info['battle_token'])){ echo('$this_key('.$this_key.') = '.print_r($this_info, true)); }
                $this_battleinfo = rpg_battle::get_index_info($this_info['battle_token']);
                //if (!empty($this_battleinfo)){ $this_battleinfo = array_replace($this_battleinfo, $this_info); }
                $temp_flags = isset($this_battleinfo['flags']) ? $this_battleinfo['flags'] : array();
                $temp_values = isset($this_battleinfo['values']) ? $this_battleinfo['values'] : array();
                $temp_counters = isset($this_battleinfo['counters']) ? $this_battleinfo['counters'] : array();
                if (!empty($this_battleinfo)){ $this_battleinfo = array_merge($this_battleinfo, $this_info); }
                else { $this_battleinfo = $this_info; }
                $this_battleinfo['flags'] = !empty($this_battleinfo['flags']) ? array_merge($this_battleinfo['flags'], $temp_flags) : $temp_flags;
                $this_battleinfo['values'] = !empty($this_battleinfo['values']) ? array_merge($this_battleinfo['values'], $temp_values) : $temp_values;
                $this_battleinfo['counters'] = !empty($this_battleinfo['counters']) ? array_merge($this_battleinfo['counters'], $temp_counters) : $temp_counters;
                //if (!is_array($this_battleinfo['battle_field_info'])){ echo('Key '.$this_key.' in $battle_options_reversed = <pre>'.print_r($battle_options_reversed, true).'</pre>'); }
                //if (!is_array($this_battleinfo['battle_field_info'])){ echo('$this_battleinfo[\'battle_field_info\'] = <pre>'.print_r($this_battleinfo, true).'</pre>'); }
                //if (!is_array($this_battleinfo['battle_field_info'])){ echo('$db->INDEX[\'BATTLES\']['.$this_info['battle_token'].'] = <pre>'.print_r($db->INDEX['BATTLES'][$this_info['battle_token']], true).'</pre>'); }
                if (!isset($this_battleinfo['battle_field_info'])){ echo print_r($this_battleinfo, true); }
                $this_fieldtoken = $this_battleinfo['battle_field_info']['field_token'];
                $this_fieldinfo =
                    !empty($mmrpg_index_fields[$this_fieldtoken])
                    ? array_replace(rpg_field::parse_index_info($mmrpg_index_fields[$this_fieldtoken]), $this_battleinfo['battle_field_info'])
                    : $this_battleinfo['battle_field_info'];
                $this_targetinfo = !empty($mmrpg_index['players'][$this_battleinfo['battle_target_player']['player_token']]) ? array_replace($mmrpg_index['players'][$this_battleinfo['battle_target_player']['player_token']], $this_battleinfo['battle_target_player']) : $this_battleinfo['battle_target_player'];

                // Collect the robot index for calculation purposes
                $this_robot_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

                // Check the GAME session to see if this battle has been completed, increment the counter if it was
                $this_battleinfo['battle_option_complete'] = rpg_prototype::battle_complete($player_token, $this_info['battle_token']);
                $this_battleinfo['battle_option_failure'] = rpg_prototype::battle_failure($player_token, $this_info['battle_token']);

                // Generate the markup fields for display
                $this_option_token = $this_battleinfo['battle_token'];
                $this_option_limit = !empty($this_battleinfo['battle_robots_limit']) ? $this_battleinfo['battle_robots_limit'] : 8;
                $this_option_frame = !empty($this_battleinfo['battle_sprite_frame']) ? $this_battleinfo['battle_sprite_frame'] : 'base';
                $this_option_status = !empty($this_battleinfo['battle_status']) ? $this_battleinfo['battle_status'] : 'enabled';
                $this_option_points = !empty($this_battleinfo['battle_points']) ? $this_battleinfo['battle_points'] : 0;
                $this_option_complete = $this_battleinfo['battle_option_complete'];
                $this_option_failure = $this_battleinfo['battle_option_failure'];
                $this_option_targets = !empty($this_targetinfo['player_robots']) ? count($this_targetinfo['player_robots']) : 0;
                $this_option_encore = isset($this_battleinfo['battle_encore']) ? $this_battleinfo['battle_encore'] : true;
                $this_option_disabled = !empty($this_option_complete) && !$this_option_encore ? true : false;
                $this_has_field_star = !empty($this_battleinfo['values']['field_star']) && !rpg_game::star_unlocked($this_battleinfo['values']['field_star']['star_token']) ? true : false;
                $this_has_dark_tower = !empty($this_battleinfo['flags']['dark_tower']) ? true : false;

                $this_option_class = 'option option_fieldback option_this-'.$player_token.'-battle-select option_'.$this_battleinfo['battle_size'].' option_'.$this_option_status.' block_'.($this_key + 1).' '.($this_option_complete && !$this_has_field_star && !$this_has_dark_tower ? 'option_complete ' : '').($this_option_disabled ? 'option_disabled '.($this_option_encore ? 'option_disabled_clickable ' : '') : '');
                $this_option_style = 'background-position: -'.mt_rand(5, 50).'px -'.mt_rand(5, 50).'px; ';
                if (!empty($this_fieldinfo['field_type'])){ $this_option_class .= 'field_type field_type_'.$this_fieldinfo['field_type'].(!empty($this_fieldinfo['field_type2']) && $this_fieldinfo['field_type2'] != $this_fieldinfo['field_type'] ? '_'.$this_fieldinfo['field_type2'] : ''); }
                else { $this_option_class .= 'field_type field_type_none'; }
                if (!empty($this_fieldinfo['field_background'])){
                    //$this_background_x = $this_background_y = -20;
                    //$this_option_style = 'background-position: 0 0; background-size: 100% auto; background-image: url(images/fields/'.$this_fieldinfo['field_background'].'/battle-field_preview.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                    $this_option_style = 'background-image: url(images/fields/'.$this_fieldinfo['field_background'].'/battle-field_preview.png?'.MMRPG_CONFIG_CACHE_DATE.') !important; ';
                }
                $this_option_label = '';
                $this_option_platform_style = '';
                if (!empty($this_fieldinfo['field_foreground'])){
                    //$this_background_x = $this_background_y = -20;
                    //$this_option_platform_style = 'background-position: 0 -76px; background-size: 100% auto; background-image: url(images/fields/'.$this_fieldinfo['field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                    $this_option_platform_style = 'background-image: url(images/fields/'.$this_fieldinfo['field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                }
                $this_option_min_level = false;
                $this_option_max_level = false;
                $this_battleinfo['battle_sprite'] = array();
                $this_targetinfo = !empty($mmrpg_index['players'][$this_targetinfo['player_token']]) ? array_merge($mmrpg_index['players'][$this_targetinfo['player_token']], $this_targetinfo) : $mmrpg_index['players']['player'];
                if ($this_targetinfo['player_token'] != 'player'){
                    $this_battleinfo['battle_sprite'][] = array('path' => 'players/'.$this_targetinfo['player_token'], 'size' => !empty($this_targetinfo['player_image_size']) ? $this_targetinfo['player_image_size'] : 40);
                }
                if (!empty($this_targetinfo['player_robots'])){

                    // Count the number of masters in this battle
                    $this_master_count = 0;
                    $this_mecha_count = 0;
                    $temp_robot_tokens = array();
                    foreach ($this_targetinfo['player_robots'] AS $robo_key => $this_robotinfo){
                        //if (empty($this_robotinfo['robot_token'])){ die('<pre>'.$this_battleinfo['battle_token'].print_r($this_robotinfo, true).'</pre>'); }
                        if ($this_robotinfo['robot_token'] == 'robot'){ unset($this_targetinfo['player_robots'][$robo_key]); continue; }
                        if (isset($this_robot_index[$this_robotinfo['robot_token']])){ $this_robotindex = rpg_robot::parse_index_info($this_robot_index[$this_robotinfo['robot_token']]); }
                        else { continue; }
                        $temp_robot_tokens[] = $this_robotinfo['robot_token'];
                        $this_robotinfo = array_merge($this_robotindex, $this_robotinfo);
                        $this_targetinfo['player_robots'][$robo_key] =  $this_robotinfo;
                        if (!empty($this_robotinfo['robot_class']) && $this_robotinfo['robot_class'] == 'mecha'){ $this_mecha_count++; }
                        elseif (empty($this_robotinfo['robot_class']) || $this_robotinfo['robot_class'] == 'master'){ $this_master_count++; }
                        unset($this_robotindex);
                    }
                    $temp_robot_tokens = array_unique($temp_robot_tokens);
                    $temp_robot_tokens_count = count($temp_robot_tokens);
                    $temp_robot_target_count = count($this_targetinfo['player_robots']);

                    // Create a list of the different robot tokens in this battle
                    // Now loop through robots again and display 'em
                    foreach ($this_targetinfo['player_robots'] AS $this_robotinfo){

                        // HIDE MECHAS
                        if (empty($this_battleinfo['flags']['starter_battle']) && empty($this_battleinfo['flags']['player_battle'])
                            && !empty($this_robotinfo['robot_class']) && $this_robotinfo['robot_class'] == 'mecha'
                            && $temp_robot_tokens_count > 1 && $this_master_count > 0){ continue; }

                        // HIDE MECHAS in FORTRESS
                        if (!empty($this_battleinfo['flags']['fortress_battle']) && !empty($this_robotinfo['robot_class']) && $this_robotinfo['robot_class'] == 'mecha'){ continue; }

                        // HIDE HIDDEN
                        if (!empty($this_robotinfo['flags']['hide_from_mission_select'])){ continue; }

                        $this_robotinfo['robot_image'] = !empty($this_robotinfo['robot_image']) ? $this_robotinfo['robot_image'] : $this_robotinfo['robot_token'];
                        //if (!empty($this_battleinfo['flags']['player_battle'])){ $this_robotinfo['robot_image'] = 'robot'; }
                        //if (!empty($this_robotinfo['flags']['hide_from_mission_'])){ $temp_path = 'robots/robot'; }
                        //else { $temp_path = 'robots/'.$this_robotinfo['robot_image']; }
                        //$temp_path = 'robots/'.(empty($this_battleinfo['flags']['player_battle']) ? $this_robotinfo['robot_image'] : 'robot');
                        $temp_path = 'robots/'.$this_robotinfo['robot_image'];
                        $temp_size = !empty($this_robotinfo['robot_image_size']) ? $this_robotinfo['robot_image_size'] : 40;
                        if (!empty($this_battleinfo['flags']['player_battle'])){
                            $temp_path = in_array($this_robotinfo['robot_token'], array('roll', 'disco', 'rhythm', 'splash-woman')) ? 'robots/robot2' : 'robots/robot';
                            $temp_size = 40;
                        }
                        $this_battleinfo['battle_sprite'][] = array('path' => $temp_path, 'size' => $temp_size);

                        $this_robot_level = !empty($this_robotinfo['robot_level']) ? $this_robotinfo['robot_level'] : 1;
                        if ($this_option_min_level === false || $this_option_min_level > $this_robot_level){ $this_option_min_level = $this_robot_level; }
                        if ($this_option_max_level === false || $this_option_max_level < $this_robot_level){ $this_option_max_level = $this_robot_level; }

                    }
                }

                // Add the field/fusion star sprite if one has been added
                if ($this_has_field_star){
                    //$this_option_complete = false;
                    $this_option_disabled = false;
                    // Check if this is a field star or fusion star
                    $temp_star_data = $this_battleinfo['values']['field_star'];
                    //die('<pre>'.print_r($temp_star_data, true).'</pre>');
                    $temp_star_kind = $temp_star_data['star_kind'];
                    // Collect the star image info from the index based on type
                    $temp_field_type_1 = !empty($temp_star_data['star_type']) ? $temp_star_data['star_type'] : 'none';
                    $temp_field_type_2 = !empty($temp_star_data['star_type2']) ? $temp_star_data['star_type2'] : $temp_field_type_1;

                    // If this is a field star, we can add sprite normally
                    if ($temp_star_kind == 'field'){

                        $temp_star_back_info = rpg_prototype::star_image($temp_field_type_1);
                        $temp_star_front_info = rpg_prototype::star_image($temp_field_type_1);
                        $temp_star_back = array('path' => 'abilities/item-star-base-'.$temp_star_front_info['sheet'], 'size' => 40, 'frame' => $temp_star_front_info['frame']);
                        $temp_star_front = array('path' => 'abilities/item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'], 'size' => 40, 'frame' => $temp_star_back_info['frame']);
                        array_unshift($this_battleinfo['battle_sprite'], $temp_star_back, $temp_star_front);

                    }
                    // Otherwise, if this is a fusion star, add it in layers
                    elseif ($temp_star_kind == 'fusion'){

                        $temp_star_back_info = rpg_prototype::star_image($temp_field_type_2);
                        $temp_star_front_info = rpg_prototype::star_image($temp_field_type_1);
                        $temp_star_back = array('path' => 'abilities/item-star-base-'.$temp_star_front_info['sheet'], 'size' => 40, 'frame' => $temp_star_front_info['frame']);
                        $temp_star_front = array('path' => 'abilities/item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'], 'size' => 40, 'frame' => $temp_star_back_info['frame']);
                        array_unshift($this_battleinfo['battle_sprite'], $temp_star_back, $temp_star_front);

                    }
                }

                // Add the dark tower sprite if one has been added
                if ($this_has_dark_tower){
                    //$this_option_complete = false;
                    $this_option_disabled = false;
                    // Add the dark tower sprite to the mission select
                    $temp_tower_sprite = array('path' => 'abilities/item-dark-tower', 'size' => 40, 'frame' => 0);
                    array_unshift($this_battleinfo['battle_sprite'], $temp_tower_sprite);
                }

                // Loop through the battle sprites and display them
                if (!empty($this_battleinfo['battle_sprite'])){
                    $temp_right = false;
                    $temp_layer = 100;
                    $temp_count = count($this_battleinfo['battle_sprite']);
                    $temp_last_size = 0;
                    foreach ($this_battleinfo['battle_sprite'] AS $temp_key => $this_battle_sprite){
                        $temp_opacity = $temp_layer == 10 ? 1 : 1 - ($temp_key * 0.09);
                        $temp_path = $this_battle_sprite['path'];
                        $temp_size = $this_battle_sprite['size'];
                        $temp_frame = !empty($this_battle_sprite['frame']) ? $this_battle_sprite['frame'] : '';
                        $temp_size_text = $temp_size.'x'.$temp_size;
                        $temp_top = -2 + (40 - $temp_size);
                        if (!preg_match('/^abilities/i', $temp_path)){
                            if ($temp_right === false){
                                //die('<pre>'.print_r($temp_right, true).'</pre>');
                                if ($temp_size == 40){
                                    $temp_right_inc =  0;
                                    $temp_right = 18 + $temp_right_inc;
                                } else {
                                    $temp_right_inc =  -1 * ceil(($temp_size - 40) * 0.5);
                                    $temp_right = 18 + $temp_right_inc;
                                }
                            } else {
                                if ($temp_size == 40){
                                    $temp_right_inc = ceil($temp_size * 0.5);
                                    $temp_right += $temp_right_inc;
                                } else {
                                    $temp_right_inc = ceil(($temp_size - 40) * 0.5); //ceil($temp_size * 0.5);
                                    $temp_right += $temp_right_inc;
                                }
                                if ($temp_size > $temp_last_size){
                                    $temp_right -= ceil(($temp_size - $temp_last_size) / 2);
                                } elseif ($temp_size < $temp_last_size){
                                    $temp_right += ceil(($temp_last_size - $temp_size) / 2);
                                }
                            }
                        } else {
                            $temp_right = 5;
                        }
                        //if ($temp_size > 40) die('<pre>'.print_r($temp_size.':'.$temp_left_inc, true).'</pre>');
                        //if (preg_match('/^abilities/i', $temp_path)){ $this_option_label .= '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_'.str_pad($temp_frame, 2, '0', STR_PAD_LEFT).' " style="background-image: url(images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: 1px; right: -3px; z-index: '.$temp_layer.'; opacity: '.$temp_opacity.';">&nbsp;</span>'; } //'.$this_battleinfo['battle_name'].'
                        //else { $this_option_label .= '<span class="sprite sprite_'.$temp_size_text.' '.($this_option_complete && !$this_has_field_star && $this_option_frame == 'base' ? 'sprite_'.$temp_size_text.'_defeat ' : 'sprite_'.$temp_size_text.'_'.$this_option_frame.' ').'" style="background-image: url(images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_top.'px; right: '.$temp_right.'px; z-index: '.$temp_layer.'; opacity: '.$temp_opacity.';">&nbsp;</span>'; } //'.$this_battleinfo['battle_name'].'
                        $temp_path_full = 'images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png';
                        // Find and replace length path names with shorter ones for smaller request sizes
                        $temp_find_paths = array(
                        'images/players/', 'images/players_shadows/', // players
                        'images/robots/', 'images/robots_shadows/', // robots
                        'images/abilities/', // abilities
                        '/sprite_left_40x40', '/sprite_left_80x80', '/sprite_left_160x160', // left sprite 40, 80, 160
                        '/sprite_right_40x40', '/sprite_right_80x80', '/sprite_right_160x160', // right sprite 40, 80, 160
                        '/mug_left_40x40', '/mug_left_80x80', '/mug_left_160x160', // left mug 40, 80, 160
                        '/mug_right_40x40', '/mug_right_80x80', '/mug_right_160x160', // right mug 40, 80, 160
                        '/icon_left_40x40', '/icon_left_80x80', '/icon_left_160x160', // left sprite 40, 80, 160
                        '/icon_right_40x40', '/icon_right_80x80', '/icon_right_160x160' // right sprite 40, 80, 160
                        );
                        $temp_replace_paths = array(
                        'i/p/', 'i/ps/', // players
                        'i/r/', 'i/rs/', // robots
                        'i/a/', // abilities
                        '/sl40', '/sl80', '/sl160', // left sprite 40, 80, 160
                        '/sr40', '/sr80', '/sr160', // right sprite 40, 80, 160
                        '/ml40', '/ml80', '/ml160', // left mug 40, 80, 160
                        '/mr40', '/mr80', '/mr160', // right mug 40, 80, 160
                        '/il40', '/il80', '/il160', // left sprite 40, 80, 160
                        '/ir40', '/ir80', '/ir160' // right sprite 40, 80, 160
                        );
                        $temp_path_full = str_replace($temp_find_paths, $temp_replace_paths, $temp_path_full);
                        if (preg_match('/^abilities/i', $temp_path)){ $this_option_label .= '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_'.str_pad($temp_frame, 2, '0', STR_PAD_LEFT).' " style="background-image: url('.$temp_path_full.'?'.MMRPG_CONFIG_CACHE_DATE.'); top: 1px; right: -3px;">&nbsp;</span>'; } //'.$this_battleinfo['battle_name'].'
                        else { $this_option_label .= '<span class="sprite sprite_'.$temp_size_text.' '.($this_option_complete && !$this_has_field_star && !$this_has_dark_tower && $this_option_frame == 'base' ? 'sprite_'.$temp_size_text.'_defeat ' : 'sprite_'.$temp_size_text.'_'.$this_option_frame.' ').'" style="background-image: url('.$temp_path_full.'?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_top.'px; right: '.$temp_right.'px;">&nbsp;</span>'; } //'.$this_battleinfo['battle_name'].'
                        $temp_layer -= 1;
                        $temp_last_size = $temp_size;
                    }
                }

                //if ($this_battleinfo['battle_token'] == 'base-spark-man') die('<pre>'.print_r(htmlentities($this_option_label), true).'</pre>');
                //$this_option_button_text = !empty($this_battleinfo['battle_button']) ? $this_battleinfo['battle_button'] : '';
                //$this_option_button_text = !empty($this_fieldinfo['field_name']) ? $this_fieldinfo['field_name'] : '';
                if (!isset($this_battleinfo['battle_robots_limit'])){ $this_battleinfo['battle_robots_limit'] = 1; }
                if (!isset($this_battleinfo['battle_points'])){ $this_battleinfo['battle_points'] = 0; }
                if (!isset($this_battleinfo['battle_zenny'])){ $this_battleinfo['battle_zenny'] = 0; }
                if (!empty($this_battleinfo['battle_button'])){ $this_option_button_text = $this_battleinfo['battle_button']; }
                elseif (!empty($this_fieldinfo['field_name'])){ $this_option_button_text = $this_fieldinfo['field_name']; }
                else { $this_option_button_text = 'Battle'; }
                if ($this_option_min_level < 1){ $this_option_min_level = 1; }
                if ($this_option_max_level > 100){ $this_option_max_level = 100; }
                $this_option_level_range = $this_option_min_level == $this_option_max_level ? 'Level '.$this_option_min_level : 'Levels '.$this_option_min_level.'-'.$this_option_max_level;
                $this_option_point_amount = number_format($this_option_points, 0, '.', ',').' Point'.($this_option_points != 1 ? 's' : '');
                //$this_option_label .= (!empty($this_option_button_text) ? '<span class="multi"><span class="maintext">'.$this_option_button_text.'</span><span class="subtext">'.$this_option_level_range.str_replace('|', '<span class="pipe">|</span>', $this_option_star_force).'</span><span class="subtext2">'.$this_option_point_amount.'</span></span>'.(!$this_has_field_star && (!$this_option_complete || ($this_option_complete && $this_option_encore)) ? '<span class="arrow"> &#9658;</span>' : '') : '<span class="single">???</span>');
                if (!empty($this_option_button_text)){ $this_option_label .= '<span class="multi"><span class="maintext">'.$this_option_button_text.'</span><span class="subtext">'.$this_option_point_amount.'</span><span class="subtext2">'.$this_option_level_range.'</span></span>'.(!$this_has_field_star && (!$this_option_complete || ($this_option_complete && $this_option_encore)) ? '<span class="arrow"> &#9658;</span>' : ''); }
                else { $this_option_label .= '<span class="single">???</span>'; }


                // Generate this options hover tooltip details
                $this_option_title = ''; //$this_battleinfo['battle_button'];
                //$this_option_title .= '$this_master_count = '.$this_master_count.'; $this_mecha_count = '.$this_mecha_count.'; ';
                //if ($this_battleinfo['battle_button'] != $this_battleinfo['battle_name']){ $this_option_title .= ' | '.$this_battleinfo['battle_name']; }
                $this_option_title .= '&laquo; '.$this_battleinfo['battle_name'].' &raquo;';
                $this_option_title .= ' <br />'.$this_fieldinfo['field_name'];
                if (!empty($this_fieldinfo['field_type'])){
                    if (!empty($this_fieldinfo['field_type2'])){ $this_option_title .= ' | '.ucfirst($this_fieldinfo['field_type']).' / '.ucfirst($this_fieldinfo['field_type2']).' Type'; }
                    else { $this_option_title .= ' | '.ucfirst($this_fieldinfo['field_type']).' Type'; }
                }
                $this_option_title .= ' | '.$this_option_level_range.' <br />';
                $this_option_title .= 'Target : '.($this_battleinfo['battle_turns_limit'] == 1 ? '1 Turn' : $this_battleinfo['battle_turns_limit'].' Turns').' with '.($this_battleinfo['battle_robots_limit'] == 1 ? '1 Robot' : $this_battleinfo['battle_robots_limit'].' Robots').' <br />';
                $this_option_title .= 'Reward : '.($this_battleinfo['battle_points'] == 1 ? '1 Point' : number_format($this_battleinfo['battle_points'], 0, '.', ',').' Points').' and '.($this_battleinfo['battle_zenny'] == 1 ? '1 Zenny' : number_format($this_battleinfo['battle_zenny'], 0, '.', ',').' Zenny');
                $this_option_title .= ' <br />'.$this_battleinfo['battle_description'].(!empty($this_battleinfo['battle_description2']) ? ' '.$this_battleinfo['battle_description2'] : '');

                $this_option_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_option_title));
                $this_option_title_tooltip = htmlentities($this_option_title, ENT_QUOTES, 'UTF-8');
                // Define the field multipliers
                $temp_field_multipliers = array();
                if (!empty($this_fieldinfo['field_multipliers'])){
                    $temp_multiplier_list = $this_fieldinfo['field_multipliers'];
                    asort($temp_multiplier_list);
                    $temp_multiplier_list = array_reverse($temp_multiplier_list, true);
                    foreach ($temp_multiplier_list AS $temp_type => $temp_multiplier){
                        if ($temp_multiplier == 1){ continue; }
                        $temp_field_multipliers[] = $temp_type.'*'.number_format($temp_multiplier, 1);
                    }
                }
                $temp_field_multipliers = !empty($temp_field_multipliers) ? implode('|', $temp_field_multipliers) : '';
                // DEBUG DEBUG
                //$this_battleinfo['battle_description'] .= json_encode($this_battleinfo['battle_rewards']);
                // Print out the option button markup with sprite and name
                $this_markup .= '<a data-chapter="'.$this_info['option_chapter'].'" data-tooltip="'.$this_option_title_tooltip.'" data-field="'.htmlentities($this_fieldinfo['field_name'], ENT_QUOTES, 'UTF-8', true).'" data-description="'.htmlentities(($this_battleinfo['battle_description'].(!empty($this_battleinfo['battle_description2']) ? ' '.$this_battleinfo['battle_description2'] : '')), ENT_QUOTES, 'UTF-8', true).'" data-multipliers="'.$temp_field_multipliers.'" data-background="'.(!empty($this_fieldinfo['field_background']) ? $this_fieldinfo['field_background'] : '').'" data-foreground="'.(!empty($this_fieldinfo['field_foreground']) ? $this_fieldinfo['field_foreground'] : '').'" class="'.$this_option_class.'" data-token="'.$this_option_token.'" data-next-limit="'.$this_option_limit.'" style="'.$this_option_style.(!empty($this_info['option_style']) ? ' '.$this_info['option_style'] : '').'"><div class="platform" style="'.$this_option_platform_style.'"><div class="chrome"><div class="inset"><label class="'.(!empty($this_battleinfo['battle_sprite']) ? 'has_image' : 'no_image').'">'.$this_option_label.'</label></div></div></div></a>'."\r\n";
                // Update the main battle option array with recent changes
                $this_battleinfo['flag_skip'] = true;
                $battle_options[$this_key] = $this_battleinfo;

            }

        }

        // Return the generated markup
        return $this_markup;
    }

    // Define a function for generating option message markup
    public static function option_message_markup($player_token, $subject, $lineone, $linetwo, $sprites = ''){
        $temp_optiontext = '<span class="multi"><span class="maintext">'.$subject.'</span><span class="subtext">'.$lineone.'</span><span class="subtext2">'.$linetwo.'</span></span>';
        return '<a class="option option_1x4 option_this-'.$player_token.'-select option_message "><div class="chrome"><div class="inset"><label class="'.(!empty($sprites) ? 'has_image' : '').'">'.$sprites.$temp_optiontext.'</label></div></div></a>'."\n";
    }

    // Define a function for generating an ability set for a given robot
    public static function generate_abilities($robot_info, $robot_level = 1, $ability_num = 1, $robot_item = ''){
        global $db;
        // Define the static variables for the ability lists
        static $mmrpg_prototype_core_abilities;
        static $mmrpg_prototype_master_support_abilities;
        static $mmrpg_prototype_mecha_support_abilities;
        static $mmrpg_prototype_darkness_abilities;
        // Define all the core and support abilities to be used in generating
        if (empty($mmrpg_prototype_core_abilities)){
            $mmrpg_prototype_core_abilities = array(
                array(
                    'rolling-cutter', 'super-throw', 'ice-breath', 'hyper-bomb', 'fire-storm', 'thunder-strike', 'time-arrow', 'oil-shooter',
                    'metal-blade', 'air-shooter', 'bubble-spray', 'quick-boomerang', 'crash-bomber', 'flash-stopper', 'atomic-fire', 'leaf-shield',
                    'needle-cannon', 'magnet-missile', 'gemini-laser', 'hard-knuckle', 'top-spin', 'search-snake', 'spark-shock', 'shadow-blade',
                    'bright-burst', 'rain-flush', 'drill-blitz', 'pharaoh-soul', 'ring-boomerang', 'dust-crusher', 'dive-missile', 'skull-barrier',
                    'cutter-shot', 'cutter-buster',
                    'freeze-shot', 'freeze-buster',
                    'crystal-shot', 'crystal-buster',
                    'flame-shot', 'flame-buster',
                    'electric-shot', 'electric-buster',
                    'space-shot', 'space-buster',
                    'laser-shot', 'laser-buster'
                    ),
                array(
                    'rising-cutter', 'super-arm', 'ice-slasher', 'danger-bomb', 'fire-chaser', 'thunder-beam', 'time-slow', 'oil-slider',
                    'bubble-lead',
                    'bubble-bomb'
                    ),
                array(
                    'cutter-overdrive',
                    'freeze-overdrive',
                    'crystal-overdrive',
                    'flame-overdrive',
                    'electric-overdrive',
                    'space-overdrive',
                    'laser-overdrive'
                    )
                );
        }
        if (empty($mmrpg_prototype_master_support_abilities)){
            $mmrpg_prototype_master_support_abilities = array(
                array(
                    'buster-shot'
                    ),
                array(
                    'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
                    ),
                array(
                    'attack-break', 'defense-break', 'speed-break', 'energy-break',
                    ),
                array(
                    'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
                    ),
                array(
                    'attack-mode', 'defense-mode', 'speed-mode', 'repair-mode',
                    ),
                array(
                    'attack-support', 'defense-support', 'speed-support', 'energy-support',
                    'attack-assault', 'defense-assault', 'speed-assault', 'energy-assault',
                    ),
                array(
                    'attack-shuffle', 'defense-shuffle', 'speed-shuffle', 'energy-shuffle'
                    ),
                array(
                    'mecha-support', 'field-support'
                    ),
                array(
                    'experience-booster', 'recovery-booster', 'damage-booster',
                    'experience-breaker', 'recovery-breaker', 'damage-breaker',
                    )
                );
        }
        if (empty($mmrpg_prototype_mecha_support_abilities)){
            $mmrpg_prototype_mecha_support_abilities = array(
                array(
                    'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
                    ),
                array(
                    'attack-break', 'defense-break', 'speed-break', 'energy-break',
                    ),
                array(
                    'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
                    )
                );
        }
        if (empty($mmrpg_prototype_darkness_abilities)){
            $mmrpg_prototype_darkness_abilities = array(
                array(
                    'dark-boost', 'dark-break', 'dark-drain'
                    )
                );
        }

        // Define the array for holding all of this robot's abilities
        $this_robot_abilities = array();
        //$temp_core_abilities = $mmrpg_prototype_core_abilities;
        //$temp_support_abilities = $mmrpg_prototype_master_support_abilities;

        // Loop through this robot's level-up abilities looking for one
        $this_robot_index = $robot_info;
        if (!empty($this_robot_index['robot_rewards']['abilities'])){
            foreach ($this_robot_index['robot_rewards']['abilities'] AS $info){
                // If this is the buster shot or too high of a level, continue
                if ($info['token'] == 'buster-shot' || $info['level'] > $robot_level){ continue; }
                // If this is an incomplete master ability, continue
                if ($this_robot_index['robot_class'] == 'master'){
                    if (!in_array($info['token'], $mmrpg_prototype_core_abilities[0]) && !in_array($info['token'], $mmrpg_prototype_core_abilities[1])){
                        continue;
                    }
                }
                // Add this ability token the list
                $this_robot_abilities[] = $info['token'];
            }
        }

        // Define a new array to hold all the addon abilities
        $this_robot_abilities_addons = array('base' => $this_robot_abilities, 'weapons' => array(), 'support' => array());

        // If we have already enough abilities, we have nothing more to do
        if (count($this_robot_abilities) >= $ability_num){

            // Simple slice to make sure we don't go over eight
            $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

        }
        // Otherwise, if we need more abilities, we generate them dynamically
        else {

            // Define the number of additional abilities to add
            $remaining_abilities = $ability_num - count($this_robot_abilities);

            // Collect the ability index for calculation purposes
            static $this_ability_index;
            if (empty($this_ability_index)){ $this_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token'); }

            // Check if this robot is holding a core
            $robot_item_core = !empty($robot_item) && preg_match('/^item-core-/i', $robot_item) ? preg_replace('/^item-core-/i', '', $robot_item) : '';

            // Define the number of core and support abilities for the robot
            if ($this_robot_index['robot_class'] == 'master'){
                foreach ($mmrpg_prototype_core_abilities AS $group_key => $group_abilities){
                    if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
                    foreach ($group_abilities AS $ability_key => $ability_token){
                        if (in_array($ability_token, $this_robot_abilities)){ continue; }
                        $ability_info = $this_ability_index[$ability_token];
                        $is_compatible = false;
                        if (!$is_compatible && in_array($ability_token, $this_robot_index['robot_abilities'])){
                            $is_compatible = true;
                        }
                        if (!$is_compatible && !empty($this_robot_index['robot_core'])){
                            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if (!$is_compatible && !empty($robot_item_core)){
                            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if ($is_compatible){ $this_robot_abilities_addons['weapons'][] = $ability_token; }
                    }
                    unset($ability_info);
                }
            }

            // Define the number of core and master support abilities for the robot
            if ($this_robot_index['robot_class'] == 'master' && $this_robot_index['robot_core'] != 'empty'){
                foreach ($mmrpg_prototype_master_support_abilities AS $group_key => $group_abilities){
                    if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
                    foreach ($group_abilities AS $ability_key => $ability_token){
                        if (in_array($ability_token, $this_robot_abilities)){ continue; }
                        $ability_info = $this_ability_index[$ability_token];
                        $is_compatible = false;
                        if (!$is_compatible && in_array($ability_token, $this_robot_index['robot_abilities'])){
                            $is_compatible = true;
                        }
                        if (!$is_compatible && !empty($this_robot_index['robot_core'])){
                            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if (!$is_compatible && !empty($robot_item_core)){
                            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
                    }
                    unset($ability_info);
                }
            }
            // Define the number of core and mecha support abilities for the robot
            elseif ($this_robot_index['robot_class'] == 'mecha' && $this_robot_index['robot_core'] != 'empty'){
                foreach ($mmrpg_prototype_mecha_support_abilities AS $group_key => $group_abilities){
                    if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
                    foreach ($group_abilities AS $ability_key => $ability_token){
                        if (in_array($ability_token, $this_robot_abilities)){ continue; }
                        $ability_info = $this_ability_index[$ability_token];
                        $is_compatible = false;
                        if (!$is_compatible && empty($ability_info['ability_type'])){
                            $is_compatible = true;
                        }
                        if (!$is_compatible && !empty($ability_info['ability_type'])){
                            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if (!$is_compatible && !empty($robot_item_core)){
                            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
                    }
                    unset($ability_info);
                }
            }

            // Define the number of darkness abilities for the robot
            if ($this_robot_index['robot_core'] == 'empty' && $this_robot_index['robot_class'] == 'master'){
                foreach ($mmrpg_prototype_darkness_abilities AS $group_key => $group_abilities){
                    if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
                    foreach ($group_abilities AS $ability_key => $ability_token){
                        if (in_array($ability_token, $this_robot_abilities)){ continue; }
                        $ability_info = rpg_ability::parse_index_info($this_ability_index[$ability_token]);
                        $is_compatible = false;
                        if (!$is_compatible && in_array($ability_token, $this_robot_index['robot_abilities'])){
                            $is_compatible = true;
                        }
                        if (!$is_compatible && !empty($this_robot_index['robot_core'])){
                            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if (!$is_compatible && !empty($robot_item_core)){
                            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
                    }
                    unset($ability_info);
                }
            }

            // Shuffle the weapons and support arrays
            $this_robot_abilities_addons['weapons'] = array_unique($this_robot_abilities_addons['weapons']);
            $this_robot_abilities_addons['support'] = array_unique($this_robot_abilities_addons['support']);
            shuffle($this_robot_abilities_addons['weapons']);
            shuffle($this_robot_abilities_addons['support']);

            // If there were no main abilities, give them an addons
            if (empty($this_robot_abilities) && !empty($this_robot_abilities_addons['weapons'])){
                $temp_token = array_shift($this_robot_abilities_addons['weapons']);
                $this_robot_abilities[] = $temp_token;
                $this_robot_abilities_addons['base'][] = $temp_token;
            }

            // Define the last addon array which will have alternating values
            $temp_addons_final = array();
            $temp_count_limit = count($this_robot_abilities_addons['weapons']) + count($this_robot_abilities_addons['support']);
            for ($i = 0; $i < $temp_count_limit; $i++){
                if (isset($this_robot_abilities_addons['weapons'][$i]) || isset($this_robot_abilities_addons['support'][$i])){
                    if (isset($this_robot_abilities_addons['support'][$i])){ $temp_addons_final[] = $this_robot_abilities_addons['support'][$i]; }
                    if (isset($this_robot_abilities_addons['weapons'][$i])){ $temp_addons_final[] = $this_robot_abilities_addons['weapons'][$i]; }
                } else {
                    break;
                }
            }

            // Combine the two arrays into one again
            //$this_robot_abilities = array_merge($this_robot_abilities_addons['base'], $this_robot_abilities_addons['weapons'], $this_robot_abilities_addons['support']);
            $this_robot_abilities = array_merge($this_robot_abilities, $temp_addons_final);
            // Crop the array to the requested length
            $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

        }

        // Return the ability array, whatever it was
        return $this_robot_abilities;

    }

    // Define a function for sorting the omega player robots
    public static function sort_player_robots($info1, $info2){
        $info1_robot_level = $info1['robot_level'];
        $info2_robot_level = $info2['robot_level'];
        $info1_robot_favourite = isset($info1['values']['flag_favourite']) ? $info1['values']['flag_favourite'] : 0;
        $info2_robot_favourite = isset($info2['values']['flag_favourite']) ? $info2['values']['flag_favourite'] : 0;
        if ($info1_robot_favourite < $info2_robot_favourite){ return 1; }
        elseif ($info1_robot_favourite > $info2_robot_favourite){ return -1; }
        elseif ($info1_robot_level < $info2_robot_level){ return 1; }
        elseif ($info1_robot_level > $info2_robot_level){ return -1; }
        else { return 0; }
    }

    // Define a function to sort prototype robots based on their current level / experience points
    public static function sort_robots_experience($info1, $info2){
        global $this_prototype_data;
        $info1_robot_level = rpg_game::robot_level($this_prototype_data['this_player_token'], $info1['robot_token']);
        $info1_robot_experience = rpg_game::robot_experience($this_prototype_data['this_player_token'], $info1['robot_token']);
        $info2_robot_level = rpg_game::robot_level($this_prototype_data['this_player_token'], $info2['robot_token']);
        $info2_robot_experience = rpg_game::robot_experience($this_prototype_data['this_player_token'], $info2['robot_token']);
        if ($info1_robot_level < $info2_robot_level){ return 1; }
        elseif ($info1_robot_level > $info2_robot_level){ return -1; }
        elseif ($info1_robot_experience < $info2_robot_experience){ return 1; }
        elseif ($info1_robot_experience > $info2_robot_experience){ return -1; }
        else { return 0; }
    }


    // Define a function to sort prototype robots based on their current level / experience points
    public static function sort_robots_position($info1, $info2){
        global $this_prototype_data;
        static $this_robot_favourites;
        if (empty($this_robot_favourites)){ $this_robot_favourites = rpg_game::robot_favourites(); }
        $temp_player_settings = rpg_game::player_settings($this_prototype_data['this_player_token']);
        $info1_robot_position = array_search($info1['robot_token'], array_keys($temp_player_settings['player_robots']));
        $info2_robot_position = array_search($info2['robot_token'], array_keys($temp_player_settings['player_robots']));
        $info1_robot_favourite = in_array($info1['robot_token'], $this_robot_favourites) ? 1 : 0;
        $info2_robot_favourite = in_array($info2['robot_token'], $this_robot_favourites) ? 1 : 0;
        if ($info1_robot_favourite < $info2_robot_favourite){ return 1; }
        elseif ($info1_robot_favourite > $info2_robot_favourite){ return -1; }
        elseif ($info1_robot_position < $info2_robot_position){ return -1; }
        elseif ($info1_robot_position > $info2_robot_position){ return 1; }
        else { return 0; }
    }

    // Define the field star image function for use in other parts of the game
    public static function star_image($type){
        static $type_order = array('none', 'copy', 'crystal', 'cutter', 'earth',
            'electric', 'explode', 'flame', 'freeze', 'impact',
            'laser', 'missile', 'nature', 'shadow', 'shield',
            'space', 'swift', 'time', 'water', 'wind');
        $type_sheet = 1;
        $type_frame = array_search($type, $type_order);
        if ($type_frame >= 10){
            $type_sheet = 2;
            $type_frame = $type_frame - 10;
        } elseif ($type_frame < 0){
            $type_sheet = 1;
            $type_frame = 0;
        }
        $temp_array = array('sheet' => $type_sheet, 'frame' => $type_frame);
        return $temp_array;
    }

    // Define a function for pulling the leaderboard players index
    public static function leaderboard_index(){
        global $db;

        // Check to see if the leaderboard index has already been pulled or not
        if (!empty($db->INDEX['LEADERBOARD']['index'])){
            $this_leaderboard_index = json_decode($db->INDEX['LEADERBOARD']['index'], true);
        } else {
            // Define the array for pulling all the leaderboard data
            $temp_leaderboard_query = 'SELECT
                mmrpg_users.user_id,
                mmrpg_users.user_name,
                mmrpg_users.user_name_clean,
                mmrpg_users.user_name_public,
                mmrpg_users.user_date_accessed,
                mmrpg_leaderboard.board_points
                FROM mmrpg_users
                LEFT JOIN mmrpg_leaderboard ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
                WHERE mmrpg_leaderboard.board_points > 0 ORDER BY mmrpg_leaderboard.board_points DESC
                ';
            // Query the database and collect the array list of all online players
            $this_leaderboard_index = $db->get_array_list($temp_leaderboard_query);
            // Update the database index cache
            $db->INDEX['LEADERBOARD']['index'] = json_encode($this_leaderboard_index);
        }

        // Return the collected leaderboard index
        return $this_leaderboard_index;

    }

    // Define a function for pulling the leaderboard players index
    public static function leaderboard_index_tokens(){
        global $db;

        // Check to see if the leaderboard index has already been pulled or not
        if (!empty($db->INDEX['LEADERBOARD']['index'])){ $this_leaderboard_index = json_decode($db->INDEX['LEADERBOARD']['index'], true); }
        else { $this_leaderboard_index = self::leaderboard_index(); }

        // Collect all the leaderboard tokens and add to array
        $this_leaderboard_tokens = array();
        foreach ($this_leaderboard_index AS $key => $info){ $this_leaderboard_tokens[] = $info['user_name_clean']; }

        // Return the collected leaderboard tokens
        return $this_leaderboard_tokens;

    }

    // Define a function for collecting the requested player's board ranking
    public static function leaderboard_rank($user_id){

        // Query the database and collect the array list of all non-bogus players
        $this_leaderboard_index = rpg_prototype::leaderboard_index();
        $this_leaderboard_points = 0;
        $this_leaderboard_list = array();
        foreach ($this_leaderboard_index AS $array){
            $this_leaderboard_list[] = $array['board_points'];
            if ($array['user_id'] == $user_id){ $this_leaderboard_points = $array['board_points']; }
        }
        $this_leaderboard_list = array_unique($this_leaderboard_list);
        sort($this_leaderboard_list);
        $this_leaderboard_list = array_reverse($this_leaderboard_list);

        // Now collect the leaderboard rank based on position
        if (in_array($this_leaderboard_points, $this_leaderboard_list)){
            $this_leaderboard_rank = array_search($this_leaderboard_points, $this_leaderboard_list);
            $this_leaderboard_rank = $this_leaderboard_rank !== false ? $this_leaderboard_rank + 1 : 0;
        } else {
            $this_leaderboard_rank = 0;
        }
        return $this_leaderboard_rank;

    }

    // Define a function for pulling the leaderboard online player
    public static function leaderboard_online(){
        global $db;
        // Check to see if the leaderboard online has already been pulled or not
        if (!empty($db->INDEX['LEADERBOARD']['online'])){
            $this_leaderboard_online = json_decode($db->INDEX['LEADERBOARD']['online'], true);
        } else {
            // Collect the leaderboard index for ranking
            $this_leaderboard_index = rpg_prototype::leaderboard_index();
            // Generate the points index and then break it down to unique for ranks
            $this_points_index = array();
            if (!empty($this_leaderboard_index)){
                foreach ($this_leaderboard_index AS $info){
                    $this_points_index[] = $info['board_points'];
                }
            }
            $this_points_index = array_unique($this_points_index);
            // Define the vars for finding the online players
            $this_time = time();
            $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
            // Loop through the collected index and pull online players
            $this_leaderboard_online = array();
            if (!empty($this_leaderboard_index)){
                foreach ($this_leaderboard_index AS $key => $board){
                    if (!empty($board['user_date_accessed']) && (($this_time - $board['user_date_accessed']) <= $this_online_timeout)){
                        $temp_userid = !empty($board['user_id']) ? $board['user_id'] : 0;
                        $temp_usertoken = $board['user_name_clean'];
                        $temp_username = !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'];
                        $temp_username = htmlentities($temp_username, ENT_QUOTES, 'UTF-8', true);
                        $temp_points = !empty($board['board_points']) ? $board['board_points'] : 0;
                        $temp_place = array_search($board['board_points'], $this_points_index) + 1;
                        $this_leaderboard_online[] = array('id' => $temp_userid, 'name' => $temp_username, 'token' => $temp_usertoken, 'points' => $temp_points, 'place' => $temp_place);
                    }
                }
            }
            // Update the database index cache
            $db->INDEX['LEADERBOARD']['online'] = json_encode($this_leaderboard_online);
        }
        // Return the collected online players if any
        return $this_leaderboard_online;
    }

    // Define a function for pulling the leaderboard custom player options
    public static function leaderboard_custom($player_token = '', $this_userid = 0){
        global $db;
        // Check to see if the leaderboard online has already been pulled or not
        if (!empty($db->INDEX['LEADERBOARD']['custom'])){
            $this_leaderboard_custom = json_decode($db->INDEX['LEADERBOARD']['custom'], true);
        } else {
            // Collect the leaderboard index for ranking
            $this_leaderboard_index = rpg_prototype::leaderboard_index();
            if (!empty($player_token)){
                $this_custom_array = !empty($_SESSION['GAME']['values']['battle_targets'][$player_token]) ? $_SESSION['GAME']['values']['battle_targets'][$player_token] : array();
            } else {
                $this_custom_array = array();
                if (!empty($_SESSION['GAME']['values']['battle_targets'])){
                    foreach ($_SESSION['GAME']['values']['battle_targets'] AS $player_token => $player_custom_array){
                        $this_custom_array = array_merge($this_custom_array, $player_custom_array);
                    }
                }
            }

            // Generate the points index and then break it down to unique for ranks
            $this_points_index = array();
            foreach ($this_leaderboard_index AS $info){ $this_points_index[] = $info['board_points']; }
            $this_points_index = array_unique($this_points_index);

            // Loop through the collected index and pull online players
            $this_leaderboard_custom = array();
            if (!empty($this_leaderboard_index)){
                foreach ($this_leaderboard_index AS $key => $board){
                    if ($board['user_id'] != $this_userid && !empty($board['user_name_clean']) && in_array($board['user_name_clean'], $this_custom_array)){
                        $temp_userid = !empty($board['user_id']) ? $board['user_id'] : 0;
                        $temp_usertoken = $board['user_name_clean'];
                        $temp_username = !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'];
                        $temp_username = htmlentities($temp_username, ENT_QUOTES, 'UTF-8', true);
                        $temp_points = !empty($board['board_points']) ? $board['board_points'] : 0;
                        $temp_place = array_search($board['board_points'], $this_points_index) + 1;
                        $this_leaderboard_custom[] = array('id' => $temp_userid, 'name' => $temp_username, 'token' => $temp_usertoken, 'points' => $temp_points, 'place' => $temp_place);
                    }
                }
            }
            // Update the database index cache
            $db->INDEX['LEADERBOARD']['custom'] = json_encode($this_leaderboard_custom);
        }
        // Return the collected online players if any
        return $this_leaderboard_custom;
    }

    // Define a function for pulling the leaderboard rival targets
    public static function leaderboard_rivals($this_leaderboard_index, $this_userid, $offset = 10){
        global $db;

        // Collect the position of the current player in the leaderboard list
        $this_leaderboard_index_position = 0;
        foreach ($this_leaderboard_index AS $key => $array){
            if ($array['user_id'] == $this_userid){
                $this_leaderboard_index_position = $key;
                break;
            }
        }

        // Collect the players before and after the current user for matchmaking
        $max_player_key = $this_leaderboard_index_position - $offset;
        $min_player_key = $this_leaderboard_index_position + $offset;
        if ($max_player_key < 0){ $min_player_key -= $max_player_key; $max_player_key = 0; }
        if ($min_player_key > count($this_leaderboard_index)){ $max_player_key -= $min_player_key - count($this_leaderboard_index); }
        $this_leaderboard_targets = $this_leaderboard_index;
        unset($this_leaderboard_targets[$this_leaderboard_index_position]);
        $this_leaderboard_targets = array_slice($this_leaderboard_targets, $max_player_key, $min_player_key);

        // Return the collected rival players
        return $this_leaderboard_targets;

    }

    // Define a function for pulling the leaderboard targets
    public static function leaderboard_targets($this_userid, $player_limit = 12, $player_sort = '', $player_campaign = ''){
        global $db;

        // Check to see if the leaderboard targets have already been pulled or not
        if (!empty($db->INDEX['LEADERBOARD']['targets'])){

            $this_leaderboard_target_players = $db->INDEX['LEADERBOARD']['targets'];

        } else {

            // Collect the leaderboard index and online players for ranking
            $this_leaderboard_index = rpg_prototype::leaderboard_index();
            $this_leaderboard_targets = array();
            $this_leaderboard_targets_ids = array();
            $this_leaderboard_targets['custom'] = rpg_prototype::leaderboard_custom($player_campaign, $this_userid);
            $this_leaderboard_targets['online'] = rpg_prototype::leaderboard_online();
            $this_leaderboard_targets['rival'] = rpg_prototype::leaderboard_rivals($this_leaderboard_index, $this_userid, 10);
            $this_leaderboard_targets_ids['custom'] = array();
            $this_leaderboard_targets_ids['online'] = array();
            $this_leaderboard_targets_ids['rival'] = array();
            if (!empty($this_leaderboard_targets['custom'])){ shuffle($this_leaderboard_targets['custom']); }
            if (!empty($this_leaderboard_targets['online'])){ shuffle($this_leaderboard_targets['online']); }
            if (!empty($this_leaderboard_targets['rival'])){ shuffle($this_leaderboard_targets['rival']); }

            //die('<pre>$this_leaderboard_targets(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_targets, true).'</pre>');

            //$this_leaderboard_include_players = array_merge($this_leaderboard_targets['online'], $this_leaderboard_targets['rival']);
            //$this_leaderboard_include_players = array_slice($this_leaderboard_include_players, 0, $player_limit);
            //shuffle($this_leaderboard_include_players);

            // Generate the custom username tokens for adding to the condition list
            $temp_include_raw = array();
            $temp_include_userids = array();
            $temp_include_usernames = array();
            $temp_include_usernames_count = 0;
            $temp_include_usernames_string = array();

            // Add the include data to the raw array
            if (!empty($this_leaderboard_targets)){
                foreach ($this_leaderboard_targets AS $kind => $players){
                    if (!empty($players)){
                        if (!isset($this_leaderboard_targets_ids[$kind])){ $this_leaderboard_targets_ids[$kind] = array(); }
                        foreach ($players AS $key => $info){
                            $id = isset($info['user_id']) ? $info['user_id'] : $info['id'];
                            $this_leaderboard_targets_ids[$kind][] = $id;
                            if (!isset($temp_include_raw[$id])){
                                $temp_include_raw[$id] = $info;
                            }
                            else { continue; }
                        }
                    }

                }
            }

            //die('<pre>$this_leaderboard_targets_ids(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_targets_ids, true).'</pre>');

            // Re-key the array to prevent looping errors
            $temp_include_raw = array_values($temp_include_raw);

            // Loop thrugh the raw array and collect filter variables
            if (!empty($temp_include_raw)){
                foreach ($temp_include_raw AS $info){
                    if (isset($info['id']) && $info['id'] != $this_userid){
                        $temp_include_usernames[] = $info['token'];
                        $temp_include_userids[] = $info['id'];
                    } elseif (isset($info['user_id']) && $info['user_id'] != $this_userid){
                        $temp_include_usernames[] = $info['user_name_clean'];
                        $temp_include_userids[] = $info['user_id'];
                    }
                }
                $temp_include_usernames_count = count($temp_include_usernames);
                if (!empty($temp_include_usernames)){
                    foreach ($temp_include_usernames AS $token){ $temp_include_usernames_string[] = "'{$token}'"; }
                    $temp_include_usernames_string = implode(',', $temp_include_usernames_string);
                } else {
                    $temp_include_usernames_string = '';
                }
            } else {
                $temp_include_usernames_string = '';
            }

            //die('<pre>$temp_include_raw(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($temp_include_raw, true).'</pre>');

            // Define the array for pulling all the leaderboard data
            $temp_leaderboard_query = 'SELECT
                    mmrpg_leaderboard.user_id,
                    mmrpg_leaderboard.board_points,
                    mmrpg_users.user_name,
                    mmrpg_users.user_name_clean,
                    mmrpg_users.user_name_public,
                    mmrpg_users.user_gender,
                    mmrpg_saves.save_values_battle_rewards AS player_rewards,
                    mmrpg_saves.save_values_battle_settings AS player_settings,
                    mmrpg_saves.save_values AS player_values,
                    mmrpg_saves.save_counters AS player_counters
                    FROM mmrpg_leaderboard
                    LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
                    LEFT JOIN mmrpg_saves ON mmrpg_users.user_id = mmrpg_saves.user_id
                    WHERE board_points > 0
                    AND mmrpg_leaderboard.user_id != '.$this_userid.'
                    '.(!empty($temp_include_usernames_string) ? 'AND mmrpg_users.user_name_clean IN ('.$temp_include_usernames_string.') ' : '').'
                    ORDER BY board_points DESC
                ';
            //AND board_points >= '.$this_player_points_min.' AND board_points <= '.$this_player_points_max.'
            //'.(!empty($temp_online_usernames_string) ? ' FIELD(user_name_clean, '.$temp_online_usernames_string.') DESC, ' : '').'
            //LIMIT '.$player_limit.'

            // Query the database and collect the array list of all online players
            //die('<pre>$temp_leaderboard_query(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($temp_leaderboard_query, true).'</pre>');
            $this_leaderboard_target_players = $db->get_array_list($temp_leaderboard_query);

            //die('<pre>$this_leaderboard_target_players(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_target_players, true).'</pre>');

            // Sort the target players based on position in userid array
            usort($this_leaderboard_target_players, function($u1, $u2) use ($temp_include_userids) {
                $id1 = isset($u1['user_id']) ? $u1['user_id'] : $u1['id'];
                $id2 = isset($u2['user_id']) ? $u2['user_id'] : $u2['id'];
                $pos1 = array_search($id1, $temp_include_userids);
                $pos2 = array_search($id2, $temp_include_userids);
                if ($pos1 > $pos2){ return 1; }
                elseif ($pos1 < $pos2){ return -1; }
                else { return 0; }
                });

            //die('<pre>$this_leaderboard_target_players(this:'.$player_campaign.'/target:'.$player_sort.') = '.print_r($this_leaderboard_target_players, true).'</pre>');

            //die('<pre>(this:'.$player_campaign.'/target:'.$player_sort.')'."\n\n".'$temp_leaderboard_query = '.print_r($temp_leaderboard_query, true).''."\n\n".'$this_leaderboard_target_players = '.print_r($this_leaderboard_target_players, true).'</pre>');

            // Loop through and decode any fields that require it
            if (!empty($this_leaderboard_target_players)){
                foreach ($this_leaderboard_target_players AS $key => $player){
                    $player['player_rewards'] = !empty($player['player_rewards']) ? json_decode($player['player_rewards'], true) : array();
                    $player['player_settings'] = !empty($player['player_settings']) ? json_decode($player['player_settings'], true) : array();
                    $player['values'] = !empty($player['player_values']) ? json_decode($player['player_values'], true) : array();
                    $player['counters'] = !empty($player['player_counters']) ? json_decode($player['player_counters'], true) : array();
                    unset($player['player_values']);
                    unset($player['player_counters']);
                    $player['player_favourites'] = !empty($player['values']['robot_favourites']) ? $player['values']['robot_favourites'] : array();
                    $player['player_starforce'] = !empty($player['values']['star_force']) ? $player['values']['star_force'] : array();
                    if (!empty($player_sort)){ $player['counters']['player_robots_count'] = !empty($player['player_rewards'][$player_sort]['player_robots']) ? count($player['player_rewards'][$player_sort]['player_robots']) : 0; }
                    $player['values']['flag_custom'] = in_array($player['user_id'], $this_leaderboard_targets_ids['custom']) ? 1 : 0;
                    $player['values']['flag_online'] = in_array($player['user_id'], $this_leaderboard_targets_ids['online']) ? 1 : 0;
                    $player['values']['flag_rival'] = in_array($player['user_id'], $this_leaderboard_targets_ids['rival']) ? 1 : 0;
                    $this_leaderboard_target_players[$key] = $player;
                }
            }

            // Update the database index cache
            //if (!empty($player_sort)){ uasort($this_leaderboard_target_players, 'mmrpg_prototype_leaderboard_targets_sort'); }
            $db->INDEX['LEADERBOARD']['targets'] = $this_leaderboard_target_players;
            //die($temp_leaderboard_query);

        }
        // Return the collected online players if any
        return $this_leaderboard_target_players;
    }


    // Define a function for sorting the target leaderboard players
    public static function leaderboard_targets_sort($player1, $player2){
        if ($player1['values']['flag_online'] < $player2['values']['flag_online']){ return 1; }
        elseif ($player1['values']['flag_online'] > $player2['values']['flag_online']){ return -1; }
        elseif ($player1['counters']['battle_points'] < $player2['counters']['battle_points']){ return -1; }
        elseif ($player1['counters']['battle_points'] > $player2['counters']['battle_points']){ return 1; }
        elseif ($player1['counters']['player_robots_count'] < $player2['counters']['player_robots_count']){ return -1; }
        elseif ($player1['counters']['player_robots_count'] > $player2['counters']['player_robots_count']){ return 1; }
        else { return 0; }
    }
    // Define a function for sorting the target leaderboard players
    public static function leaderboard_targets_sort_online($player1, $player2){
        if ($player1['values']['flag_online'] < $player2['values']['flag_online']){ return 1; }
        elseif ($player1['values']['flag_online'] > $player2['values']['flag_online']){ return -1; }
        else { return 0; }
    }



    // Define a function for determining a player's battle music
    public static function get_player_music($player_token, $session_token = 'GAME'){
        global $mmrpg_index, $db;

        $temp_session_key = $player_token.'_target-robot-omega_prototype';
        $temp_robot_omega = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
        $temp_robot_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

        // Count the games representaed and order by count
        $temp_game_counters = array();
        foreach ($temp_robot_omega AS $omega){
            if (empty($omega['robot'])){ continue; }
            $index = rpg_robot::parse_index_info($temp_robot_index[$omega['robot']]);
            $game = strtolower($index['robot_game']);
            if (!isset($temp_game_counters[$game])){ $temp_game_counters[$game] = 0; }
            $temp_game_counters[$game] += 1;
        }

        //die('<pre>$temp_game_counters = '.print_r($temp_game_counters, true).'</pre>');

        if (empty($temp_game_counters)){
            if ($player_token == 'dr-light'){ $temp_game_counters['mm01'] = 1; }
            if ($player_token == 'dr-wily'){ $temp_game_counters['mm02'] = 1; }
            if ($player_token == 'dr-cossack'){ $temp_game_counters['mm04'] = 1; }
        }

        asort($temp_game_counters, SORT_NUMERIC);

        //echo("\n".'-------'.$player_token.'-------'."\n".'<pre>$temp_game_counters = '.print_r($temp_game_counters, true).'</pre>'."\n");

        // Get the last element in the array
        end($temp_game_counters);
        $most_key = key($temp_game_counters);
        $most_count = $temp_game_counters[$most_key];

        //echo("\n".'<pre>$most_key = '.print_r($most_key, true).'; $most_count = '.print_r($most_count, true).'</pre>'."\n");

        $most_options = array($most_key);
        foreach ($temp_game_counters AS $key => $count){ if ($key != $most_key && $count >= $most_count){ $most_options[] = $key; } }
        if (count($most_options) > 1){ $most_key = $most_options[array_rand($most_options, 1)];  }

        //echo("\n".'<pre>$most_options = '.print_r($most_options, true).'</pre>'."\n");

        //echo("\n".'<pre>$most_key = '.print_r($most_key, true).'; $most_count = '.print_r($most_count, true).'</pre>'."\n");

        return $most_key;

    }

    // Define a function for determining a player's battle music
    public static function get_player_mission_music($player_token, $session_token = 'GAME'){
        $most_key = rpg_prototype::get_player_music($player_token, $session_token);
        return 'stage-select-'.$most_key;
    }


    // Define a function for determining a player's boss music
    public static function get_player_boss_music($player_token, $session_token = 'GAME'){
        $most_key = rpg_prototype::get_player_music($player_token, $session_token);
        return 'boss-theme-'.$most_key;
    }

    // Define a function for checking the battle's prototype points total
    public static function database_summoned($robot_token = ''){
        // Define static variables amd populate if necessary
        static $this_count_array;
        // Return the current point total for thisgame
        $session_token = rpg_game::session_token();
        // Check if the array is empty and populate if not
        if (empty($this_count_array)){
            // Define the array to hold all the summon counts
            $this_count_array = array();
            // If the robot database array is not empty, loop through it
            if (!empty($_SESSION[$session_token]['values']['robot_database'])){
                foreach ($_SESSION[$session_token]['values']['robot_database'] AS $token => $info){
                    if (!empty($info['robot_summoned'])){ $this_count_array[$token] = $info['robot_summoned']; }
                }
            }
        }
        // If the robot token was not empty
        if (!empty($robot_token)){
            // If the array exists, return the count
            if (!empty($this_count_array[$robot_token])){ return $this_count_array[$robot_token]; }
            // Otherwise, return zero
            else { return 0; }
        }
        // Otherwise, return the full array
        else {
            // Return the count array
            return $this_count_array;
        }
    }


    // Define a function for collecting robot sprite markup
    public static function player_select_markup($prototype_data, $player_token, $this_button_size = '1x4'){
        global $mmrpg_index, $db;
        $session_token = rpg_game::session_token();

        // Collect the player info
        $player_info = rpg_player::get_index_info($player_token);
        switch ($player_token){
            case 'dr-light': $player_symbol = '&hearts;'; $player_face = ':D'; break;
            case 'dr-wily': $player_symbol = '&clubs;'; $player_face = 'XD'; break;
            case 'dr-cossack': $player_symbol = '&diams;'; $player_face = '8D'; break;
            default: $player_symbol = ''; $player_face = ':|'; break;
        }


        // Generate the markup for each of the robot sprites
        $temp_offset_x = 14;
        $temp_offset_z = 50;
        $temp_offset_y = -2;
        $temp_offset_opacity = 0.75;
        $text_sprites_markup = '';
        $temp_player_robots = rpg_game::robot_tokens_unlocked($player_token);
        //$_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'];

        // Collect the base index data for these robots
        $temp_token_string = array();
        foreach ($temp_player_robots AS $token){ $temp_token_string[] = "'{$token}'"; }
        $temp_token_string = implode(', ', $temp_token_string);
        $temp_robot_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_token IN ({$temp_token_string}) AND robot_flag_complete = 1;", 'robot_token');

        foreach ($temp_player_robots AS $key => $robot_token){
            $index = rpg_robot::parse_index_info($temp_robot_index[$robot_token]);
            $rewards = rpg_game::robot_rewards($player_token, $robot_token);
            $settings = rpg_game::robot_settings($player_token, $robot_token);
            $info = array_merge($index, $rewards, $settings);

        exit(PHP_EOL.PHP_EOL.
        $player_token.' : '.$robot_token.' = '.PHP_EOL.
        '$index = '.print_r($index, true).
        '$rewards = '.print_r($rewards, true).
        '$settings = '.print_r($settings, true).
        PHP_EOL.PHP_EOL);

            if (rpg_game::robot_unlocked($player_token, $robot_token)){
                $temp_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
                $temp_size_text = $temp_size.'x'.$temp_size;
                $temp_offset_x += $temp_size > 40 ? 0 : 20;
                $temp_offset_y = $temp_size > 40 ? -42 : -2;
                $temp_offset_z -= 1;
                $temp_offset_opacity -= 0.05;
                if ($temp_offset_opacity <= 0){ $temp_offset_opacity = 0; break; }
                $text_sprites_markup .= '<span class="sprite sprite_nobanner sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(i/r/'.(!empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token']).'/sr'.$temp_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_offset_y.'px; right: '.$temp_offset_x.'px;">'.$info['robot_name'].'</span>';
                if ($temp_size > 40){ $temp_offset_x += 20;  }
            }
        }

        exit(PHP_EOL.PHP_EOL.
        $player_token.' = '.print_r($temp_robot_index, true)
        .PHP_EOL.PHP_EOL);

        // Generate the markup for the rest of the player container
        $text_robots_unlocked = number_format($prototype_data['robots_unlocked'], 0, '.', ',').' Robot'.($prototype_data['robots_unlocked'] != 1 ? 's' : '');
        $text_abilities_unlocked = number_format($prototype_data['abilities_unlocked'], 0, '.', ',').' Ability'.($prototype_data['abilities_unlocked'] != 1 ? 's' : '');
        $text_points_unlocked = number_format($prototype_data['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['points_unlocked'] != 1 ? 's' : '');
        $text_battles_complete = number_format($prototype_data['battles_complete'], 0, '.', ',').' Mission'.($prototype_data['battles_complete'] != 1 ? 's' : '');
        $text_player_special = $prototype_data['prototype_complete'] ? true : false;
        $text_player_music = rpg_prototype::get_player_mission_music($player_token, $session_token);
        $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url(images/players/'.$player_token.'/sprite_right_40x40.png); top: -2px; right: 14px;">'.$player_info['player_name'].'</span>';
        $text_sprites_markup .= $text_sprites_markup;
        $text_player_subtext = $text_robots_unlocked;
        $text_player_subtext = $text_abilities_unlocked;

        // Put it all together for the player select markup
        $player_select_markup = '';
        $player_select_markup .= '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$prototype_data['battles_complete'].'" class="option option_'.$this_button_size.' option_this-player-select option_this-'.$player_token.'-player-select option_'.$player_token.' block_1" data-token="'.$player_token.'">';
        $player_select_markup .= '<div class="platform"><div class="chrome"><div class="inset">';
            $player_select_markup .= '<label class="has_image">';
                $player_select_markup .= '<span class="multi">';
                    $player_select_markup .= $text_sprites_markup;
                    $player_select_markup .= '<span class="maintext">';
                        $player_select_markup .= $player_info['player_name'].(!empty($text_player_special) ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! '.$player_face.'">'.$player_symbol.'</span>' : '').'</span><span class="subtext">'.$text_player_subtext.'</span><span class="subtext2">'.$text_points_unlocked.'</span></span><span class="arrow">&#9658;</span></label>';
        $player_select_markup .= '</div></div></div>';
        $player_select_markup .= '</a>'."\n";

        // Return the generated markup
        return $player_select_markup;

    }


    // Define a function for displaying prototype robot button markup on the select screen
    public static function robot_select_markup($this_prototype_data){
        // Refence the global config and index objects for easy access
        global $db;

        // Define the temporary robot markup string
        $this_robots_markup = '';

        // Collect the robot index for calculation purposes
        $this_robot_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

        // Collect the ability index for calculation purposes
        $this_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

        // Loop through and display the available robot options for this player
        $temp_robot_option_count = count($this_prototype_data['robot_options']);
        $temp_player_favourites = rpg_game::robot_favourites();
        foreach ($this_prototype_data['robot_options'] AS $key => $info){
            $info = array_merge($this_robot_index[$info['robot_token']], $info);
            if (!isset($info['original_player'])){ $info['original_player'] = $this_prototype_data['this_player_token']; }
            $this_option_class = 'option option_this-robot-select option_this-'.$info['original_player'].'-robot-select option_'.($temp_robot_option_count == 1 ? '1x4' : ($this_prototype_data['robots_unlocked'] <= 2 ? '1x2' : '1x1')).' option_'.$info['robot_token'].' block_'.($key + 1);
            $this_option_style = '';
            $this_option_token = $info['robot_id'].'_'.$info['robot_token'];
            $this_option_image = !empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token'];
            $this_option_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
            $temp_size = $this_option_size;
            $temp_size_text = $temp_size.'x'.$temp_size;
            $temp_top = -2 + (40 - $temp_size);
            $temp_right_inc = $temp_size > 40 ? ceil(($temp_size * 0.5) - 60) : 0;
            $temp_right = 15 + $temp_right_inc;
            $this_robot_name = $info['robot_name'];
            $this_robot_rewards = rpg_game::robot_rewards($this_prototype_data['this_player_token'], $info['robot_token']);
            $this_robot_settings = rpg_game::robot_settings($this_prototype_data['this_player_token'], $info['robot_token']);
            $this_robot_experience = rpg_game::robot_experience($this_prototype_data['this_player_token'], $info['robot_token']);
            $this_robot_level = rpg_game::robot_level($this_prototype_data['this_player_token'], $info['robot_token']);
            $this_experience_required = rpg_prototype::calculate_experience_required($this_robot_level);
            $this_robot_abilities = rpg_game::abilities_unlocked($this_prototype_data['this_player_token'], $info['robot_token']);
            $text_robot_special = $this_robot_level >= 100 || !empty($this_robot_rewards['flags']['reached_max_level']) ? true : false;
            $this_robot_experience = $this_robot_level >= 100 ? '<span style="position: relative; bottom: 0; font-size: 120%;">&#8734;</span>' : $this_robot_experience;
            $this_robot_experience_title = $this_robot_level >= 100 ? '&#8734;' : $this_robot_experience;
            $this_robot_favourite = in_array($info['robot_token'], $temp_player_favourites) ? true : false;
            $this_robot_name .= $this_robot_favourite ? ' <span style="position: relative; bottom: 2px; font-size: 11px;">&hearts;</span>' : '';
            $this_robot_name .= $text_robot_special ? ' <span style="position: relative; bottom: 2px; font-size: 9px;" title="Congratulations!!! :D">&#9733;</span>' : '';
            $this_robot_item = !empty($info['robot_item']) ? $info['robot_item'] : '';
            $this_robot_energy = $info['robot_energy'];
            $this_robot_attack = $info['robot_attack'];
            $this_robot_defense = $info['robot_defense'];
            $this_robot_speed = $info['robot_speed'];
            $this_robot_core = !empty($info['robot_core']) ? $info['robot_core'] : '';
            $this_robot_core2 = !empty($info['robot_core2']) ? $info['robot_core2'] : '';
            $temp_level = $this_robot_level - 1;
            $this_robot_energy += ceil($temp_level * (0.05 * $this_robot_energy));
            $this_robot_attack += ceil($temp_level * (0.05 * $this_robot_attack));
            $this_robot_defense += ceil($temp_level * (0.05 * $this_robot_defense));
            $this_robot_speed += ceil($temp_level * (0.05 * $this_robot_speed));
            if (!empty($this_robot_settings['robot_item'])){ $this_robot_item = $this_robot_settings['robot_item']; }
            if (!empty($this_robot_rewards['robot_energy'])){ $this_robot_energy += $this_robot_rewards['robot_energy']; }
            if (!empty($this_robot_rewards['robot_attack'])){ $this_robot_attack += $this_robot_rewards['robot_attack']; }
            if (!empty($this_robot_rewards['robot_defense'])){ $this_robot_defense += $this_robot_rewards['robot_defense']; }
            if (!empty($this_robot_rewards['robot_speed'])){ $this_robot_speed += $this_robot_rewards['robot_speed']; }
            if ($this_prototype_data['this_player_token'] == 'dr-light'){ $this_robot_defense += ceil(0.25 * $this_robot_defense); }
            if ($this_prototype_data['this_player_token'] == 'dr-wily'){ $this_robot_attack += ceil(0.25 * $this_robot_attack); }
            if ($this_prototype_data['this_player_token'] == 'dr-cossack'){ $this_robot_speed += ceil(0.25 * $this_robot_speed); }
            $this_robot_energy = $this_robot_energy > MMRPG_SETTINGS_STATS_MAX ? MMRPG_SETTINGS_STATS_MAX : $this_robot_energy;
            $this_robot_attack = $this_robot_attack > MMRPG_SETTINGS_STATS_MAX ? MMRPG_SETTINGS_STATS_MAX : $this_robot_attack;
            $this_robot_defense = $this_robot_defense > MMRPG_SETTINGS_STATS_MAX ? MMRPG_SETTINGS_STATS_MAX : $this_robot_defense;
            $this_robot_speed = $this_robot_speed > MMRPG_SETTINGS_STATS_MAX ? MMRPG_SETTINGS_STATS_MAX : $this_robot_speed;
            if (!empty($this_robot_settings['robot_image'])){ $this_option_image = $this_robot_settings['robot_image']; }
            if (!empty($this_robot_item) && preg_match('/^item-core-/i', $this_robot_item)){
                $item_core_type = preg_replace('/^item-core-/i', '', $this_robot_item);
                if (empty($this_robot_core2)){ //$this_robot_core != 'copy' &&
                    $this_robot_core2 = $item_core_type;
                }
            }
            $this_robot_abilities_current = !empty($info['robot_abilities']) ? array_keys($info['robot_abilities']) : array('buster-shot');
            $this_option_title = ''; //-- Basics -------------------------------  <br />';
            $this_option_title .= $info['robot_name']; //''.$info['robot_number'].' '.$info['robot_name'];
            $this_option_title .= ' ('.(!empty($this_robot_core) ? ucfirst($this_robot_core).' Core' : 'Neutral Core').')';
            $this_option_title .= ' <br />Level '.$this_robot_level.' | '.$this_robot_experience_title.' / '.$this_experience_required.' Exp'.(!empty($this_robot_favourite_title) ? ' '.$this_robot_favourite_title : '');
            if (!empty($this_robot_item) && isset($this_ability_index[$this_robot_item])){ $this_option_title .= ' | + '.$this_ability_index[$this_robot_item]['ability_name'].' '; }
            $this_option_title .= ' <br />E : '.$this_robot_energy.' | A : '.$this_robot_attack.' | D : '.$this_robot_defense.' | S: '.$this_robot_speed;
            if (!empty($this_robot_abilities_current)){
                $this_option_title .= ' <hr />'; // <hr />-- Abilities ------------------------------- <br />';
                $temp_counter = 1;
                foreach ($this_robot_abilities_current AS $token){
                    if (empty($token) || !isset($this_ability_index[$token])){ continue; }
                    $temp_info = rpg_ability::parse_index_info($this_ability_index[$token]);
                    $this_option_title .= $temp_info['ability_name'];
                    if ($temp_counter % 4 == 0){ $this_option_title .= ' <br />'; }
                    elseif ($temp_counter < count($this_robot_abilities_current)){ $this_option_title .= ' | '; }
                    $temp_counter++;
                }
            }
            $this_option_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_option_title));
            $this_option_title_tooltip = htmlentities($this_option_title, ENT_QUOTES, 'UTF-8');
            $this_option_label = '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(i/r/'.$this_option_image.'/sr'.$temp_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_top.'px; right: '.$temp_right.'px;">'.$info['robot_name'].'</span>';
            $this_option_label .= '<span class="multi">';
                $this_option_label .= '<span class="maintext">'.$this_robot_name.'</span>';
                $this_option_label .= '<span class="subtext">Level '.$this_robot_level.'</span>';
                $this_option_label .= '<span class="subtext2">'.$this_robot_experience.'/'.$this_experience_required.' Exp</span>';
            $this_option_label .= '</span>';
            $this_option_label .= '<span class="arrow">&#9658;</span>';
            //$this_robots_markup .= '<a class="'.$this_option_class.'" data-child="true" data-token="'.$this_option_token.'" title="'.$this_option_title_plain.'" data-tooltip="'.$this_option_title_tooltip.'" style="'.$this_option_style.'">';
            $this_robots_markup .= '<a class="'.$this_option_class.'" data-child="true" data-token="'.$this_option_token.'" style="'.$this_option_style.'">';
            $this_robots_markup .= '<div class="chrome chrome_type robot_type_'.(!empty($this_robot_core) ? $this_robot_core : 'none').(!empty($this_robot_core2) ? '_'.$this_robot_core2 : '').'" data-tooltip="'.$this_option_title_tooltip.'"><div class="inset"><label class="has_image">'.$this_option_label.'</label></div></div>';
            $this_robots_markup .= '</a>'."\r\n";
        }

        // Loop through and display any option padding cells
        //if ($this_prototype_data['robots_unlocked'] >= 3){
        if ($temp_robot_option_count >= 3){
            //$this_prototype_data['padding_num'] = $this_prototype_data['robots_unlocked'] <= 8 ? 4 : 2;
            $this_prototype_data['padding_num'] = 4;
            $this_prototype_data['robots_padding'] = $temp_robot_option_count % $this_prototype_data['padding_num'];
            if (!empty($this_prototype_data['robots_padding'])){
                $counter = ($temp_robot_option_count % $this_prototype_data['padding_num']) + 1;
                for ($counter; $counter <= $this_prototype_data['padding_num']; $counter++){
                    $this_option_class = 'option option_this-robot-select option_this-'.$this_prototype_data['this_player_token'].'-robot-select option_1x1 option_disabled block_'.$counter;
                    $this_option_style = '';
                    $this_robots_markup .= '<a class="'.$this_option_class.'" style="'.$this_option_style.'">';
                    $this_robots_markup .= '<div class="platform"><div class="chrome"><div class="inset"><label>&nbsp;</label></div></div></div>';
                    $this_robots_markup .= '</a>'."\r\n";
                }
            }
        }

        // Return the generated markup
        return $this_robots_markup;

    }

}
?>