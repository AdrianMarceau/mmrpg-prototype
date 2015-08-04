<?
// Count the number of completed battle options for this group and update the variable
$battle_options_reversed = $battle_options; //array_reverse($battle_options);
foreach ($battle_options_reversed AS $this_key => $this_info){
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
    $this_battleinfo = mmrpg_battle::get_index_info($this_info['battle_token']);
    //if (!empty($this_battleinfo)){ $this_battleinfo = array_replace($this_battleinfo, $this_info); }
    $temp_flags = isset($this_battleinfo['flags']) ? $this_battleinfo['flags'] : array();
    $temp_values = isset($this_battleinfo['values']) ? $this_battleinfo['values'] : array();
    $temp_counters = isset($this_battleinfo['counters']) ? $this_battleinfo['counters'] : array();
    if (!empty($this_battleinfo)){ $this_battleinfo = array_merge($this_battleinfo, $this_info); }
    else { $this_battleinfo = $this_info; }
    $this_battleinfo['flags'] = !empty($this_battleinfo['flags']) ? array_merge($this_battleinfo['flags'], $temp_flags) : $temp_flags;
    $this_battleinfo['values'] = !empty($this_battleinfo['values']) ? array_merge($this_battleinfo['values'], $temp_values) : $temp_values;
    $this_battleinfo['counters'] = !empty($this_battleinfo['counters']) ? array_merge($this_battleinfo['counters'], $temp_counters) : $temp_counters;
    //if (!is_array($this_battleinfo['battle_field_base'])){ echo('Key '.$this_key.' in $battle_options_reversed = <pre>'.print_r($battle_options_reversed, true).'</pre>'); }
    //if (!is_array($this_battleinfo['battle_field_base'])){ echo('$this_battleinfo[\'battle_field_base\'] = <pre>'.print_r($this_battleinfo, true).'</pre>'); }
    //if (!is_array($this_battleinfo['battle_field_base'])){ echo('$DB->INDEX[\'BATTLES\']['.$this_info['battle_token'].'] = <pre>'.print_r($DB->INDEX['BATTLES'][$this_info['battle_token']], true).'</pre>'); }
    $this_fieldtoken = $this_battleinfo['battle_field_base']['field_token'];
    $this_fieldinfo =
      !empty($mmrpg_index_fields[$this_fieldtoken])
      ? array_replace(mmrpg_field::parse_index_info($mmrpg_index_fields[$this_fieldtoken]), $this_battleinfo['battle_field_base'])
      : $this_battleinfo['battle_field_base'];
    $this_targetinfo = !empty($mmrpg_index['players'][$this_battleinfo['battle_target_player']['player_token']]) ? array_replace($mmrpg_index['players'][$this_battleinfo['battle_target_player']['player_token']], $this_battleinfo['battle_target_player']) : $this_battleinfo['battle_target_player'];

    // Collect the robot index for calculation purposes
    $this_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

    // Check the GAME session to see if this battle has been completed, increment the counter if it was
    $this_battleinfo['battle_option_complete'] = mmrpg_prototype_battle_complete($player_token, $this_info['battle_token']);
    $this_battleinfo['battle_option_failure'] = mmrpg_prototype_battle_failure($player_token, $this_info['battle_token']);

    // Generate the markup fields for display
    $this_option_token = $this_battleinfo['battle_token'];
    $this_option_limit = !empty($this_battleinfo['battle_robot_limit']) ? $this_battleinfo['battle_robot_limit'] : 8;
    $this_option_frame = !empty($this_battleinfo['battle_sprite_frame']) ? $this_battleinfo['battle_sprite_frame'] : 'base';
    $this_option_status = !empty($this_battleinfo['battle_status']) ? $this_battleinfo['battle_status'] : 'enabled';
    $this_option_points = !empty($this_battleinfo['battle_points']) ? $this_battleinfo['battle_points'] : 0;
    $this_option_complete = $this_battleinfo['battle_option_complete'];
    $this_option_failure = $this_battleinfo['battle_option_failure'];
    $this_option_targets = !empty($this_targetinfo['player_robots']) ? count($this_targetinfo['player_robots']) : 0;
    $this_option_encore = isset($this_battleinfo['battle_encore']) ? $this_battleinfo['battle_encore'] : true;
    $this_option_disabled = !empty($this_option_complete) && !$this_option_encore ? true : false;
    $this_has_field_star = !empty($this_battleinfo['values']['field_star']) && !mmrpg_prototype_star_unlocked($this_battleinfo['values']['field_star']['star_token']) ? true : false;
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
    $this_option_star_boost = !empty($this_targetinfo['player_starforce']) ? array_sum($this_targetinfo['player_starforce']) : 0;
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
        if (isset($this_robot_index[$this_robotinfo['robot_token']])){ $this_robotindex = mmrpg_robot::parse_index_info($this_robot_index[$this_robotinfo['robot_token']]); }
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

        $temp_star_back_info = mmrpg_prototype_star_image($temp_field_type_1);
        $temp_star_front_info = mmrpg_prototype_star_image($temp_field_type_1);
        $temp_star_back = array('path' => 'abilities/item-star-base-'.$temp_star_front_info['sheet'], 'size' => 40, 'frame' => $temp_star_front_info['frame']);
        $temp_star_front = array('path' => 'abilities/item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'], 'size' => 40, 'frame' => $temp_star_back_info['frame']);
        array_unshift($this_battleinfo['battle_sprite'], $temp_star_back, $temp_star_front);

      }
      // Otherwise, if this is a fusion star, add it in layers
      elseif ($temp_star_kind == 'fusion'){

        $temp_star_back_info = mmrpg_prototype_star_image($temp_field_type_2);
        $temp_star_front_info = mmrpg_prototype_star_image($temp_field_type_1);
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
    if (!isset($this_battleinfo['battle_robot_limit'])){ $this_battleinfo['battle_robot_limit'] = 1; }
    if (!isset($this_battleinfo['battle_points'])){ $this_battleinfo['battle_points'] = 0; }
    if (!isset($this_battleinfo['battle_zenny'])){ $this_battleinfo['battle_zenny'] = 0; }
    if (!empty($this_battleinfo['battle_button'])){ $this_option_button_text = $this_battleinfo['battle_button']; }
    elseif (!empty($this_fieldinfo['field_name'])){ $this_option_button_text = $this_fieldinfo['field_name']; }
    else { $this_option_button_text = 'Battle'; }
    if ($this_option_min_level < 1){ $this_option_min_level = 1; }
    if ($this_option_max_level > 100){ $this_option_max_level = 100; }
    $this_option_level_range = $this_option_min_level == $this_option_max_level ? 'Level '.$this_option_min_level : 'Levels '.$this_option_min_level.'-'.$this_option_max_level;
    $this_option_star_force = !empty($this_targetinfo['player_starforce']) ? ' | +'.number_format(($this_option_star_boost * MMRPG_SETTINGS_STARS_ATTACKBOOST), 0, '.', ',').' Boost' : '';
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
    $this_option_title .= ' | '.$this_option_level_range.' <br />'; //.$this_option_star_force;
    $this_option_title .= 'Target : '.($this_battleinfo['battle_turns'] == 1 ? '1 Turn' : $this_battleinfo['battle_turns'].' Turns').' with '.($this_battleinfo['battle_robot_limit'] == 1 ? '1 Robot' : $this_battleinfo['battle_robot_limit'].' Robots').' <br />';
    $this_option_title .= 'Reward : '.($this_battleinfo['battle_points'] == 1 ? '1 Point' : number_format($this_battleinfo['battle_points'], 0, '.', ',').' Points').' and '.($this_battleinfo['battle_zenny'] == 1 ? '1 Zenny' : number_format($this_battleinfo['battle_zenny'], 0, '.', ',').' Zenny');

    $this_option_title .= ' <br />'.$this_battleinfo['battle_description'].(!empty($this_battleinfo['battle_description2']) ? ' '.$this_battleinfo['battle_description2'] : '');

    /*
    if (!empty($this_option_complete) || !empty($this_option_failure) || !empty($this_has_field_star)){
      $this_option_title .= ' <hr />&laquo; Battle Records &raquo;';
      $this_option_title .= ' <br />Cleared : '.(!empty($this_option_complete['battle_count']) ? ($this_option_complete['battle_count'] == 1 ? '1 Time' : $this_option_complete['battle_count'].' Times') : '0 Times');
      $this_option_title .= ' | Failed : '.(!empty($this_option_failure['battle_count']) ? ($this_option_failure['battle_count'] == 1 ? '1 Time' : $this_option_failure['battle_count'].' Times') : '0 Times');
      if (!empty($this_option_complete)){
        if (!empty($this_option_complete['battle_max_points'])){
          $this_option_title .= ' <br />Max Points : '.(!empty($this_option_complete['battle_max_points']) ? number_format($this_option_complete['battle_max_points']) : 0).'';
          $this_option_title .= ' | Min Points : '.(!empty($this_option_complete['battle_min_points']) ? number_format($this_option_complete['battle_min_points']) : 0).'';
        }
        if (!empty($this_option_complete['battle_max_turns'])){
          $this_option_title .= ' <br />Max Turns : '.(!empty($this_option_complete['battle_max_turns']) ? number_format($this_option_complete['battle_max_turns']) : 0).'';
          $this_option_title .= ' | Min Turns : '.(!empty($this_option_complete['battle_min_turns']) ? number_format($this_option_complete['battle_min_turns']) : 0).'';
        }
        if (!empty($this_option_complete['battle_max_robots'])){
          $this_option_title .= ' <br />Max Robots : '.(!empty($this_option_complete['battle_max_robots']) ? number_format($this_option_complete['battle_max_robots']) : 0).'';
          $this_option_title .= ' | Min Robots : '.(!empty($this_option_complete['battle_min_robots']) ? number_format($this_option_complete['battle_min_robots']) : 0).'';
        }
      }
    }
    */

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

?>