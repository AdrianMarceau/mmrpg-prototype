<?
// BATTLE
$battle = array(
  'battle_name' => 'Battle',
  'battle_button' => '',
  'battle_size' => '1x4',
  'battle_encore' => false,
  'battle_status' => 'disabled',
  'battle_class' => 'system',
  'battle_description' => 'Default battle object.',
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'field'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => 1, 'robot_token' => 'robot')
      )
    ),
  );
?>