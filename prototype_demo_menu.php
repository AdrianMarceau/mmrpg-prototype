<?
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
  $this_prototype_data['robots_markup'] = mmrpg_prototype_robot_select_markup($this_prototype_data);

  // Add all these options to the global prototype data variable
  $prototype_data['demo'] = $this_prototype_data;

?>