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
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'dark-frag', 'robot_name' => 'Dark Frag A', 'robot_level' => 2, 'robot_abilities' => array('dark-boost')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'met', 'robot_level' => 1, 'robot_abilities' => array('met-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'dark-frag', 'robot_name' => 'Dark Frag B', 'robot_level' => 2, 'robot_abilities' => array('dark-boost')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'met', 'robot_level' => 1, 'robot_abilities' => array('met-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'dark-frag', 'robot_name' => 'Dark Frag C', 'robot_level' => 2, 'robot_abilities' => array('dark-boost')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 6), 'robot_token' => 'met', 'robot_level' => 1, 'robot_abilities' => array('met-shot'))
      )
    ),
  'battle_rewards' => array(
    )
  );
?>