<?
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  // Define global prototype data variable for the demo
  $prototype_data['demo'] = $this_prototype_data = array();

  // -- DEMO BATTLE OPTIONS -- //

  /*
  // Define the robot options and counter for Dr. Light mode
  $this_prototype_data['this_player_token'] = 'dr-light';
  $this_prototype_data['missions_markup'] = '';
  $this_prototype_data['robots_unlocked'] = mmrpg_prototype_robots_unlocked($this_prototype_data['this_player_token']);
  $this_prototype_data['points_unlocked'] = mmrpg_prototype_player_points($this_prototype_data['this_player_token']);
  $this_prototype_data['battles_complete'] = mmrpg_prototype_battles_complete($this_prototype_data['this_player_token']);
  $this_prototype_data['this_player_token'] = $this_prototype_data['this_player_token'];
  $this_prototype_data['this_player_field'] = 'light-laboratory';
  $this_prototype_data['target_player_token'] = 'dr-wily';
  $this_prototype_data['battle_phase'] = 0;
  $this_prototype_data['battle_options'] = array();
  $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
  $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];
  $this_prototype_data['ability_addons_one'] = array('attack-boost', 'defense-boost', 'speed-boost');
  $this_prototype_data['ability_addons_two'] = array('attack-break', 'defense-break', 'speed-break');
  $this_prototype_data['ability_addons_three'] = array('buster-shot', 'energy-boost', 'energy-break');
  $this_prototype_data['ability_addons_four'] = array('attack-mode', 'defense-mode', 'speed-mode');
  $this_prototype_data['ability_addons_five'] = array('repair-mode', 'recovery-booster');
  */
  
  // -- DR. LIGHT BATTLE OPTIONS -- //

  // Define the robot options and counter for Dr. Light mode
  $this_prototype_data['this_player_token'] = 'dr-light';
  $this_prototype_data['this_player_field'] = 'light-laboratory';
  $this_prototype_data['target_player_token'] = 'dr-wily';
  $this_prototype_data['battle_phase'] = 0;
  $this_prototype_data['battle_options'] = array();
  $this_prototype_data['missions_markup'] = '';
  $this_prototype_data['battles_complete'] = mmrpg_prototype_battles_complete($this_prototype_data['this_player_token']);
  $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
  $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];
  $this_prototype_data['robots_unlocked'] = mmrpg_prototype_robots_unlocked($this_prototype_data['this_player_token']);
  $this_prototype_data['points_unlocked'] = mmrpg_prototype_player_points($this_prototype_data['this_player_token']);
  $this_prototype_data['ability_addons_one'] = array('attack-boost', 'defense-boost', 'speed-boost');
  $this_prototype_data['ability_addons_two'] = array('attack-break', 'defense-break', 'speed-break');
  $this_prototype_data['ability_addons_three'] = array('buster-shot', 'energy-boost', 'energy-break');
  $this_prototype_data['ability_addons_four'] = array('attack-mode', 'defense-mode', 'speed-mode');
  $this_prototype_data['ability_addons_five'] = array('repair-mode', 'recovery-booster');

  // If the final battle was completed, update the flag, else set to false
  $this_prototype_data['demo_complete'] = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], 'demo-battle-iv') ? true : false;

  // Define the battle options and counter for Dr. Light mode
  $this_prototype_data['battles_complete'] = mmrpg_prototype_battles_complete($this_prototype_data['this_player_token']);
  $this_prototype_data['battle_options'] = array();

  // If there were save file corruptions, reset
  if (empty($this_prototype_data['robots_unlocked'])){
    mmrpg_reset_game_session($this_save_filepath);
    header('Location: prototype.php');
    exit();
  }
  
  // If the demo was complete, generate the option event message
  if ($this_prototype_data['battles_complete'] >= 4){

    // Generate the prototype complete message
    $this_prototype_data['battle_options'][] = array(
      'option_type' => 'message',
      'option_maintext' => 'Mega Man RPG Prototype Demo Complete! Thank You For Playing!'
      );

  }
  // Generate the welcome message for the demo mode menu
  else {

    // Generate the prototype complete message
    $flag_multi = $this_prototype_data['battles_complete'] > 0 ? true : false;
    $this_prototype_data['battle_options'][] = array(
      'option_type' => 'message',
      'option_maintext' => 'Welcome to the Mega Man RPG Prototype\'s Demo Mode!',
      );

  }
  
  // DEMO BATTLES
  if (mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], 'demo-battle-iii')){ $this_prototype_data['battle_options']['demo-battle-iv'] = array('battle_token' => 'demo-battle-iv'); }
  if (mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], 'demo-battle-ii')){ $this_prototype_data['battle_options']['demo-battle-iii'] = array('battle_token' => 'demo-battle-iii'); }
  if (mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], 'demo-battle-i')){ $this_prototype_data['battle_options']['demo-battle-ii'] = array('battle_token' => 'demo-battle-ii'); }
  $this_prototype_data['battle_options']['demo-battle-i'] = array('battle_token' => 'demo-battle-i');
  
  // Define the robot options and counter for Dr. Light mode
  $this_prototype_data['robot_options'] = !empty($mmrpg_index['players'][$this_prototype_data['this_player_token']]['player_robots']) ? $mmrpg_index['players'][$this_prototype_data['this_player_token']]['player_robots'] : array();
  //die(print_r($this_prototype_data['robot_options'], true));
  foreach ($this_prototype_data['robot_options'] AS $key => $info){
    if (!mmrpg_prototype_robot_unlocked($this_prototype_data['this_player_token'], $info['robot_token'])){ unset($this_prototype_data['robot_options'][$key]); continue; }
    $temp_settings = mmrpg_prototype_robot_settings($this_prototype_data['this_player_token'], $info['robot_token']);
    $this_prototype_data['robot_options'][$key]['original_player'] = !empty($temp_settings['original_player']) ? $temp_settings['original_player'] : $this_prototype_data['this_player_token'];
    $this_prototype_data['robot_options'][$key]['robot_abilities'] = !empty($temp_settings['robot_abilities']) ? $temp_settings['robot_abilities'] : array();
  }
  $this_prototype_data['robot_options'] = array_values($this_prototype_data['robot_options']);

  //die(print_r($this_prototype_data['robot_options'], true));

  
  
  // -- DEMO MISSION SELECT -- //

  // Define the variable to hold this player's mission markup and music
  $this_prototype_data['missions_markup'] .= mmrpg_prototype_options_markup($this_prototype_data['battle_options'], $this_prototype_data['this_player_token']);
  $this_prototype_data['missions_music'] = 'misc/stage-select-mm01';

  
  
  // -- DEMO ROBOT SELECT -- //

  // Generate the markup for this player's robot select screen
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this_prototype_data['robots_markup'] = mmrpg_prototype_robot_select_markup($this_prototype_data);
  
  /*
  // Define the variable to hold this player's mission markup and music
  $this_prototype_data['robots_markup'] = '';

  // Loop through and display the available robot options for this player
  $temp_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
  foreach ($this_prototype_data['robot_options'] AS $key => $info){
    $index = mmrpg_robot::parse_index_info($temp_robot_index[$info['robot_token']]);
    $info = array_replace($index, $info);
    $this_option_class = 'option option_this-robot-select option_this-'.$this_prototype_data['this_player_token'].'-robot-select option_'.($this_prototype_data['robots_unlocked'] == 1 ? '1x4' : ($this_prototype_data['robots_unlocked'] <= 2 ? '1x2' : '1x1')).' option_'.$info['robot_token'].' block_'.($key + 1);
    $this_option_style = '';
    $this_option_token = $info['robot_id'].'_'.$info['robot_token'];
    $this_option_image = !empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token'];
    $this_option_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
    $temp_size = $this_option_size;
    $temp_size_text = $temp_size.'x'.$temp_size;
    $temp_top = -2 + (40 - $temp_size);
    $temp_right_inc = $temp_size > 40 ? ceil(($temp_size - 40) * 0.5) : 0;
    $temp_right = 15 + $temp_right_inc;
    $this_robot_experience = mmrpg_prototype_robot_experience($this_prototype_data['this_player_token'], $info['robot_token']);
    $this_robot_level = mmrpg_prototype_robot_level($this_prototype_data['this_player_token'], $info['robot_token']);
    $this_robot_abilities = mmrpg_prototype_abilities_unlocked($this_prototype_data['this_player_token'], $info['robot_token']);
    $text_robot_special = $this_robot_level >= 100 ? true : false;
    $this_robot_experience = $this_robot_level >= 100 ? '<span style="position: relative; bottom: 0; font-size: 120%;">&#8734;</span>' : $this_robot_experience;
    $this_robot_experience_title = $this_robot_level >= 100 ? '&#8734;' : $this_robot_experience;
    $this_robot_energy = $info['robot_energy'];
    $this_robot_attack = $info['robot_attack'];
    $this_robot_defense = $info['robot_defense'];
    $this_robot_speed = $info['robot_speed'];
    $this_option_title = $info['robot_name'].' | '.$info['robot_number'].' | Level '.$this_robot_level.' | '.$this_robot_experience_title.'/1000 Exp |--';
    $this_option_title .= $this_robot_energy.' Energy | '.$this_robot_attack.' Attack | '.$this_robot_defense.' Defense | '.$this_robot_speed.' Speed |--';
    $this_option_title .= (!empty($info['robot_core']) ? ucfirst($info['robot_core']).' Core' : '').' |-- ';
    $this_option_title = str_replace(' ', '&nbsp;', $this_option_title);
    $this_option_title = str_replace('--', ' ', $this_option_title);
    $info['robot_name'] .= $text_robot_special ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! :D">&hearts;</span>' : '';
    $this_option_label = '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(images/robots/'.$this_option_image.'/sprite_right_'.$temp_size_text.'.png); top: '.$temp_top.'px; right: '.$temp_right.'px;">'.$info['robot_name'].'</span><span class="multi"><span class="maintext">'.$info['robot_name'].'</span><span class="subtext">Level '.$this_robot_level.'</span><span class="subtext2">'.$this_robot_experience.'/1000 Exp</span></span><span class="arrow">&#9658;</span>';
    $this_prototype_data['robots_markup'] .= '<a class="'.$this_option_class.'" data-child="true" data-token="'.$this_option_token.'" title="'.$this_option_title.'" style="'.$this_option_style.'"><div class="chrome"><div class="inset"><label class="has_image">'.$this_option_label.'</label></div></div></a>'."\r\n";
  }
  // Loop through and display any option padding cells
  if ($this_prototype_data['robots_unlocked'] >= 3){
    $this_prototype_data['padding_num'] = $this_prototype_data['robots_unlocked'] <= 8 ? 4 : 2;
    $this_prototype_data['robots_padding'] = $this_prototype_data['robots_unlocked'] % $this_prototype_data['padding_num'];
    if (!empty($this_prototype_data['robots_padding'])){
      $counter = ($this_prototype_data['robots_unlocked'] % $this_prototype_data['padding_num']) + 1;
      for ($counter; $counter <= $this_prototype_data['padding_num']; $counter++){
        $this_option_class = 'option option_this-robot-select option_this-'.$this_prototype_data['this_player_token'].'-robot-select option_1x1 option_disabled block_'.$counter;
        $this_option_style = '';
        $this_prototype_data['robots_markup'] .= '<a class="'.$this_option_class.'" style="'.$this_option_style.'"><div><label>&nbsp;</label></div></a>'."\r\n";
      }
    }
  }
  */

  // Add all these options to the global prototype data variable
  $prototype_data['demo'] = $this_prototype_data;

?>