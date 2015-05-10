<?
// PROTOTYPE BATTLE 5 : DEMO BATTLE II
$battle = array(
  'battle_name' => 'Demo Battle II',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Wily\'s team of reprogrammed robot masters!',
  'battle_turns' => 6,
  'battle_points' => 200,
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'abandoned-warehouse'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-wily',
    'player_switch' => 2,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'cut-man', 'robot_level' => 2, 'robot_abilities' => array('rolling-cutter', 'defense-boost', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'metal-man', 'robot_level' => 2, 'robot_abilities' => array('metal-blade', 'attack-boost', 'buster-shot'))
      )
    ),
  'battle_rewards' => array(
    )
  );
?>