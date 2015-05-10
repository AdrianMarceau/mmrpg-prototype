<?
// PROTOTYPE BATTLE 5 : VS PLAYER
$battle = array(
  'battle_name' => 'Player',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_counts' => false,
  'battle_description' => 'The ghost of another player has challenged you!  Defeat them in battle and collect the rewards!',
  'battle_turns' => 16,
  'battle_points' => 60000,
  'battle_field_base' => array('field_id' => 1000, 'field_token' => 'prototype-complete'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_switch' => 2,
    'player_name' => 'Player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'mega-man', 'robot_image' => 'robot', 'robot_name' => '???', 'robot_level' => 1, 'robot_abilities' => array('mega-buster', 'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'proto-man', 'robot_image' => 'robot', 'robot_name' => '???',  'robot_level' => 1, 'robot_abilities' => array('mega-buster', 'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost'))
      )
    ),
  'battle_rewards' => array(
    )
  );
?>