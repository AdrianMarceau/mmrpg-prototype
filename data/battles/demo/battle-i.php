<?
// PROTOTYPE BATTLE 5 : DEMO BATTLE I
$battle = array(
  'battle_name' => 'Demo Battle',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the rogue Met attacking the lab!',
  'battle_turns' => 3,
  'battle_points' => 100,
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'intro-field', 'field_music' => 'intro-field-dr-light', 'field_multipliers' => array()),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_switch' => 2,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'met', 'robot_level' => 1, 'robot_abilities' => array('met-shot')),
      )
    ),
  'battle_rewards' => array(
    )
  );
?>