<?
// PROTOTYPE BATTLE 5 : DEMO BATTLE III
$battle = array(
  'battle_name' => 'Demo Battle III',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Wily\'s team of reprogrammed robot masters!',
  'battle_turns' => 6,
  'battle_points' => 400,
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'orb-city'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-wily',
    'player_switch' => 3,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'crash-man', 'robot_level' => 3, 'robot_abilities' => array('crash-bomber', 'attack-boost', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'ice-man', 'robot_level' => 3, 'robot_abilities' => array('ice-slasher', 'defense-boost', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'bomb-man', 'robot_level' => 3, 'robot_abilities' => array('hyper-bomb', 'speed-boost', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'wood-man', 'robot_level' => 3, 'robot_abilities' => array('leaf-shield', 'attack-boost', 'buster-shot'))
      )
    ),
  'battle_rewards' => array(
    )
  );
?>