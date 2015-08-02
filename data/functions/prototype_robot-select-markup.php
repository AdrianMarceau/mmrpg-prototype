<?
// Define a function for displaying prototype robot button markup on the select screen
function mmrpg_prototype_robot_select_markup($this_prototype_data){
  // Refence the global config and index objects for easy access
  global $DB;

  // Define the temporary robot markup string
  $this_robots_markup = '';

  // Collect the robot index for calculation purposes
  $this_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

  // Collect the ability index for calculation purposes
  $this_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

  // Loop through and display the available robot options for this player
  $temp_robot_option_count = count($this_prototype_data['robot_options']);
  $temp_player_favourites = mmrpg_prototype_robot_favourites();
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
    $this_robot_rewards = mmrpg_prototype_robot_rewards($this_prototype_data['this_player_token'], $info['robot_token']);
    $this_robot_settings = mmrpg_prototype_robot_settings($this_prototype_data['this_player_token'], $info['robot_token']);
    $this_robot_experience = mmrpg_prototype_robot_experience($this_prototype_data['this_player_token'], $info['robot_token']);
    $this_robot_level = mmrpg_prototype_robot_level($this_prototype_data['this_player_token'], $info['robot_token']);
    $this_experience_required = mmrpg_prototype_calculate_experience_required($this_robot_level);
    $this_robot_abilities = mmrpg_prototype_abilities_unlocked($this_prototype_data['this_player_token'], $info['robot_token']);
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
        $temp_info = mmrpg_ability::parse_index_info($this_ability_index[$token]);
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
?>