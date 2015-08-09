<?
/*
 * ROBOT CLASS FUNCTION CANVAS MARKUP
 * public function canvas_markup($options, $player_data){}
 */

// Define the variable to hold the console robot data
$this_data = array();
$this_target_options = !empty($options['this_ability']->target_options) ? $options['this_ability']->target_options : array();
$this_damage_options = !empty($options['this_ability']->damage_options) ? $options['this_ability']->damage_options : array();
$this_recovery_options = !empty($options['this_ability']->recovery_options) ? $options['this_ability']->recovery_options : array();
$this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

// Define and calculate the simpler markup and positioning variables for this robot
$this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'robot';
$this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
$this_data['flags'] = $this->flags;
$this_data['counters'] = $this->counters;
$this_data['values'] = $this->values;
$this_data['robot_id'] = $this->robot_id;
$this_data['robot_token'] = $this->robot_token;
$this_data['robot_id_token'] = $this->robot_id.'_'.$this->robot_token;
$this_data['robot_key'] = !empty($this->robot_key) ? $this->robot_key : 0;
$this_data['robot_core'] = !empty($this->robot_core) ? $this->robot_core : 'none';
$this_data['robot_class'] = !empty($this->robot_class) ? $this->robot_class : 'master';
$this_data['robot_stance'] = !empty($this->robot_stance) ? $this->robot_stance : 'base';
$this_data['robot_frame'] = !empty($this->robot_frame) ? $this->robot_frame : 'base';
$this_data['robot_frame_index'] = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
$this_data['robot_frame_classes'] = !empty($this->robot_frame_classes) ? $this->robot_frame_classes : '';
$this_data['robot_frame_styles'] = !empty($this->robot_frame_styles) ? $this->robot_frame_styles : '';
$this_data['robot_detail_styles'] = !empty($this->robot_detail_styles) ? $this->robot_detail_styles : '';
$this_data['robot_image'] = $this->robot_image;
$this_data['robot_image_overlay'] = !empty($this->robot_image_overlay) ? $this->robot_image_overlay : array(0);
$this_data['robot_float'] = $this->player->player_side;
$this_data['robot_direction'] = $this->player->player_side == 'left' ? 'right' : 'left';
$this_data['robot_status'] = $this->robot_status;
$this_data['robot_position'] = !empty($this->robot_position) ? $this->robot_position : 'bench';
$this_data['robot_action'] = 'scan_'.$this->robot_id.'_'.$this->robot_token;
$this_data['robot_size'] = $this_data['robot_position'] == 'active' ? ($this->robot_image_size * 2) : $this->robot_image_size;
$this_data['robot_size_base'] = $this->robot_image_size;
$this_data['robot_size_path'] = ($this->robot_image_size * 2).'x'.($this->robot_image_size * 2);
//$this_data['robot_scale'] = $this_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_data['robot_key']) / 8) * 0.5);
//$this_data['robot_title'] = $this->robot_number.' '.$this->robot_name.' (Lv. '.$this->robot_level.')';
$this_data['robot_title'] = $this->robot_name.' (Lv. '.$this->robot_level.')';
$this_data['robot_title'] .= ' <br />'.(!empty($this_data['robot_core']) && $this_data['robot_core'] != 'none' ? ucfirst($this_data['robot_core']).' Core' : 'Neutral Core');
$this_data['robot_title'] .= ' | '.ucfirst($this_data['robot_position']).' Position';
$temp_energy_max = !empty($this->flags['robot_stat_max_energy']) ? ' &#9733;' : '';
$temp_attack_max = !empty($this->flags['robot_stat_max_attack']) ? ' &#9733;' : '';
$temp_defense_max = !empty($this->flags['robot_stat_max_defense']) ? ' &#9733;' : '';
$temp_speed_max = !empty($this->flags['robot_stat_max_speed']) ? ' &#9733;' : '';

// Calculate the canvas offset variables for this robot
$temp_data = $this->battle->canvas_markup_offset($this_data['robot_key'], $this_data['robot_position'], $this_data['robot_size']);
$this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
$this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
$this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];
$this_data['canvas_offset_rotate'] = 0;
$this_data['robot_scale'] = $temp_data['canvas_scale'];

// Calculate the zoom properties for the robot sprite
$zoom_size = $this->robot_image_size * 2;
$frame_index = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
$this_data['robot_sprite_size'] = ceil($this_data['robot_scale'] * $zoom_size);
$this_data['robot_sprite_width'] = ceil($this_data['robot_scale'] * $zoom_size);
$this_data['robot_sprite_height'] = ceil($this_data['robot_scale'] * $zoom_size);
$this_data['robot_file_width'] = ceil($this_data['robot_scale'] * $zoom_size * count($frame_index));
$this_data['robot_file_height'] = ceil($this_data['robot_scale'] * $zoom_size);

/* DEBUG
$this_data['robot_title'] = $this->robot_name
  .' | ID '.str_pad($this->robot_id, 3, '0', STR_PAD_LEFT).''
  //.' | '.strtoupper($this->robot_position)
  .' | '.$this->robot_energy.' LE'
  .' | '.$this->robot_attack.' AT'
  .' | '.$this->robot_defense.' DF'
  .' | '.$this->robot_speed.' SP';
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

// If the robot is defeated, move its sprite across the field
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
$this_data['energy_fraction'] = $this->robot_energy.' / '.$this->robot_base_energy;
$this_data['energy_percent'] = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
if ($this_data['energy_percent'] == 100 && $this->robot_energy < $this->robot_base_energy){ $this_data['energy_percent'] = 99; }
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
  $this_data['weapons_fraction'] = $this->robot_weapons.' / '.$this->robot_base_weapons;
  $this_data['weapons_percent'] = floor(($this->robot_weapons / $this->robot_base_weapons) * 100);
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
  if ($this->robot_level < 100){
    $required_experience = mmrpg_prototype_calculate_experience_required($this->robot_level);
    $this_data['experience_fraction'] = $this->robot_experience.' / '.$required_experience;
    $this_data['experience_percent'] = floor(($this->robot_experience / $required_experience) * 100);
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

  // Only generate a sprite if the robot is not disabled
  if (empty($this_data['flags']['apply_disabled_state'])){

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

    if ($this_data['robot_float'] == 'left'){

      $this_data['experience_class'] = 'experience';
      $this_data['experience_style'] = 'background-position: '.$this_data['experience_x_position'].'px '.$this_data['experience_y_position'].'px;';

      $this_data['energy_title'] = $this_data['energy_fraction'].' LE'.$temp_energy_max.' | '.$this_data['energy_percent'].'%';
      $this_data['robot_title'] .= ' <br />'.$this_data['energy_fraction'].' LE'.$temp_energy_max.'';

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

      $this_data['robot_title'] .= ' <br />'.$this->robot_attack.' / '.$this->robot_base_attack.' AT';
      $this_data['robot_title'] .= ' | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF';
      $this_data['robot_title'] .= ' | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP';

    }
    elseif ($this_data['robot_float'] == 'right'){

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

      $this_data['robot_title'] .= ' <br />'.$this->robot_attack.' / '.$this->robot_base_attack.' AT';
      $this_data['robot_title'] .= ' | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF';
      $this_data['robot_title'] .= ' | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP';

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
      $shadow_token = 'shadow-'.$this->robot_class;
      if ($this->robot_class == 'mecha'){ $shadow_image_token = preg_replace('/(-2|-3)$/', '', $this_data['robot_image']); }
      elseif (strstr($this_data['robot_image'], '_')){ list($shadow_image_token) = explode('_', $this_data['robot_image']); }
      else { $shadow_image_token = $this_data['robot_image']; }
      //$shadow_image_token = $this->robot_class == 'mecha' ? preg_replace('/(-2|-3)$/', '', $this_data['robot_image']) : $this_data['robot_image'];
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
    foreach ($this->player->values['robots_active'] AS $info){
      if ($info['robot_position'] == 'active'){ $temp_player_active_robots = true; }
    }

    // Only show the robot details if active or the target of an attack
    $show_details = false;
    if (isset($options['this_ability_target']) && $options['this_ability_target'] == $this_data['robot_id_token']){ $show_details = true; }
    elseif (!isset($options['this_ability_target']) || $this_data['robot_position'] == 'active'){ $show_details = true; }
    elseif ($temp_player_active_robots == false && $this_data['robot_frame'] == 'damage'){ $show_details = true; }
    if ($show_details){

      // Define the mugshot and detail variables for the GUI
      $details_data = $this_data;
      $details_data['robot_file'] = 'images/robots/'.$details_data['robot_image'].'/sprite_'.$details_data['robot_direction'].'_'.$details_data['robot_size'].'x'.$details_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
      $details_data['robot_details'] = '<div class="robot_name">'.$this->robot_name.'</div>';
      $details_data['robot_details'] .= '<div class="robot_level robot_type robot_type_'.($this->robot_level >= 100 ? 'electric' : 'none').'">Lv. '.$this->robot_level.'</div>';
      $details_data['robot_details'] .= '<div class="'.$details_data['energy_class'].'" style="'.$details_data['energy_style'].'" title="'.$details_data['energy_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$this_data['energy_tooltip_type'].'">'.$details_data['energy_title'].'</div>';
      $details_data['robot_details'] .= '<div class="'.$details_data['weapons_class'].'" style="'.$details_data['weapons_style'].'" title="'.$details_data['weapons_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_weapons">'.$details_data['weapons_title'].'</div>';
      if ($this_data['robot_float'] == 'left'){ $details_data['robot_details'] .= '<div class="'.$details_data['experience_class'].'" style="'.$details_data['experience_style'].'" title="'.$details_data['experience_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_experience">'.$details_data['experience_title'].'</div>'; }
      $details_data['robot_details_extended'] = '';

      /*
      $robot_attack_markup = '<div class="robot_attack'.($this->robot_attack < 1 ? ' robot_attack_break' : ($this->robot_attack < ($this->robot_base_attack / 2) ? ' robot_attack_break_chance' : '')).'">'.str_pad($this->robot_attack, 3, '0', STR_PAD_LEFT).'</div>';
      $robot_defense_markup = '<div class="robot_defense'.($this->robot_defense < 1 ? ' robot_defense_break' : ($this->robot_defense < ($this->robot_base_defense / 2) ? ' robot_defense_break_chance' : '')).'">'.str_pad($this->robot_defense, 3, '0', STR_PAD_LEFT).'</div>';
      $robot_speed_markup = '<div class="robot_speed'.($this->robot_speed < 1 ? ' robot_speed_break' : ($this->robot_speed < ($this->robot_base_speed / 2) ? ' robot_speed_break_chance' : '')).'">'.str_pad($this->robot_speed, 3, '0', STR_PAD_LEFT).'</div>';
      */

      // Define whether or not this robot should display smalltext
      $temp_display_smalltext = false;
      if (strlen($this->robot_attack) > 4){ $temp_display_smalltext = true;  }
      elseif (strlen($this->robot_defense) > 4){ $temp_display_smalltext = true;  }
      elseif (strlen($this->robot_speed) > 4){ $temp_display_smalltext = true;  }

      // Define attack variables and markup
      $temp_attack_break = $this->robot_attack < 1 ? true : false;
      $temp_attack_break_chance = $this->robot_attack < ($this->robot_base_attack / 2) ? true : false;
      $temp_attack_percent = round(($this->robot_attack / $this->robot_base_attack) * 100);
      if ($this_data['robot_float'] == 'left'){ $temp_attack_title = $this->robot_attack.' / '.$this->robot_base_attack.' AT'.$temp_attack_max.' | '.$temp_attack_percent.'%'.($temp_attack_break ? ' | BREAK!' : ''); }
      elseif ($this_data['robot_float'] == 'right'){ $temp_attack_title = ($temp_attack_break ? 'BREAK! | ' : '').$temp_attack_percent.'% | '.$this->robot_attack.' / '.$this->robot_base_attack.' AT'.$temp_attack_max.''; }
      $robot_attack_markup = '<div class="robot_attack'.($temp_attack_break ? ' robot_attack_break' : ($temp_attack_break_chance ? ' robot_attack_break_chance' : '')).($temp_display_smalltext ? ' robot_attack_smalltext' : '').'" title="'.$temp_attack_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_attack">'.$this->robot_attack.'</div>';

      // Define attack variables and markup
      $temp_defense_break = $this->robot_defense < 1 ? true : false;
      $temp_defense_break_chance = $this->robot_defense < ($this->robot_base_defense / 2) ? true : false;
      $temp_defense_percent = round(($this->robot_defense / $this->robot_base_defense) * 100);
      if ($this_data['robot_float'] == 'left'){ $temp_defense_title = $this->robot_defense.' / '.$this->robot_base_defense.' DF'.$temp_defense_max.' | '.$temp_defense_percent.'%'.($temp_defense_break ? ' | BREAK!' : ''); }
      elseif ($this_data['robot_float'] == 'right'){ $temp_defense_title = ($temp_defense_break ? 'BREAK! | ' : '').$temp_defense_percent.'% | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF'.$temp_defense_max.''; }
      $robot_defense_markup = '<div class="robot_defense'.($temp_defense_break ? ' robot_defense_break' : ($temp_defense_break_chance ? ' robot_defense_break_chance' : '')).($temp_display_smalltext ? ' robot_defense_smalltext' : '').'" title="'.$temp_defense_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_defense">'.$this->robot_defense.'</div>';

      // Define attack variables and markup
      $temp_speed_break = $this->robot_speed < 1 ? true : false;
      $temp_speed_break_chance = $this->robot_speed < ($this->robot_base_speed / 2) ? true : false;
      $temp_speed_percent = round(($this->robot_speed / $this->robot_base_speed) * 100);
      if ($this_data['robot_float'] == 'left'){ $temp_speed_title = $this->robot_speed.' / '.$this->robot_base_speed.' SP'.$temp_speed_max.' | '.$temp_speed_percent.'%'.($temp_speed_break ? ' | BREAK!' : ''); }
      elseif ($this_data['robot_float'] == 'right'){ $temp_speed_title = ($temp_speed_break ? 'BREAK! | ' : '').$temp_speed_percent.'% | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP'.$temp_speed_max.''; }
      $robot_speed_markup = '<div class="robot_speed'.($temp_speed_break ? ' robot_speed_break' : ($temp_speed_break_chance ? ' robot_speed_break_chance' : '')).($temp_display_smalltext ? ' robot_speed_smalltext' : '').'" title="'.$temp_speed_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_speed">'.$this->robot_speed.'</div>';

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
      if (!empty($this->robot_item)){
        $temp_item_info = mmrpg_ability::get_index_info($this->robot_item);
        $details_data['item_title'] = $temp_item_info['ability_name'];
        $details_data['item_type'] = !empty($temp_item_info['ability_type']) ? $temp_item_info['ability_type'] : 'none';
        $details_data['item_type2'] = !empty($temp_item_info['ability_type2']) ? $temp_item_info['ability_type2'] : '';
        $details_data['item_title_type'] = $details_data['item_type'];
        $details_data['item_file'] = 'images/abilities/'.$this->robot_item.'/icon_'.$details_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $details_data['item_class'] = 'sprite size40 mugshot '.$details_data['robot_float'].' ';
        $details_data['item_style'] = 'background-image: url('.$details_data['item_file'].'); ';
        if (!empty($details_data['item_type2'])){
          if ($details_data['item_title_type'] != 'none'){ $details_data['item_title_type'] .= ' '.$details_data['item_type2']; }
          else { $details_data['item_title_type'] = $details_data['item_type2']; }
        }
        $item_markup = '<div class="robot_item">';
          $item_markup .= '<div class="wrap type '.$details_data['item_title_type'].'">';
            $item_markup .= '<div class="'.$details_data['item_class'].'" style="'.$details_data['item_style'].'" title="'.$details_data['item_title'].'" data-tooltip-type="type '.$details_data['item_title_type'].'">&nbsp;</div>';
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
      if (!empty($details_data['robot_details_extended'])){ echo '<div data-detailsid="'.$this_data['robot_id'].'" class="sprite details robot_details_extended robot_details_extended_'.$details_data['robot_float'].' '.$extended_class.'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').'><div class="container">'.$details_data['robot_details_extended'].'</div></div>'; }
      echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.str_replace('80x80', '40x40', $details_data['mugshot_class']).' robot_mugshot_type robot_type robot_type_'.$this_data['robot_core'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').' data-tooltip="'.$details_data['robot_title_tooltip'].'"><div class="sprite">&nbsp;</div></div>';
      //echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'" title="'.$details_data['robot_title_plain'].'" data-tooltip="'.$details_data['robot_title_tooltip'].'">'.$details_data['robot_token'].'</div>';
      echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'">'.$details_data['robot_token'].'</div>';

      // Update the main data array with this markup
      $this_data['details'] = $details_data;
    }


  }

// Collect the generated robot markup
$this_data['robot_markup'] = trim(ob_get_clean());

?>