<?
// PROTOTYPE BATTLE 5 : DEMO BATTLE IV
$battle = array(
  'battle_name' => 'Demo Battle IV',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Wily\'s team of reprogrammed robot masters!',
  'battle_turns' => 12,
  'battle_points' => 600,
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'oil-wells'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-wily',
    'player_switch' => 4,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'oil-man', 'robot_level' => 4, 'robot_abilities' => array('oil-slider', 'oil-shooter', 'defense-boost', 'attack-break', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'bubble-man', 'robot_level' => 4, 'robot_abilities' => array('bubble-lead', 'bubble-spray', 'attack-boost', 'defense-break', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'fire-man', 'robot_level' => 4, 'robot_abilities' => array('fire-storm', 'speed-boost', 'speed-break', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'elec-man', 'robot_level' => 4, 'robot_abilities' => array('thunder-beam', 'defense-boost', 'attack-break', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'heat-man', 'robot_level' => 4, 'robot_abilities' => array('atomic-fire', 'attack-boost', 'defense-break', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 6), 'robot_token' => 'wood-man', 'robot_level' => 4, 'robot_abilities' => array('leaf-shield', 'speed-boost', 'speed-break', 'buster-shot'))
      )
    ),
  'battle_rewards' => array(
    )
  );
?>