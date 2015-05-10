<?
// PROTOTYPE BATTLE 5 : DEMO BATTLE V
$battle = array(
  'battle_name' => 'Demo Battle V',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Wily\'s team of reprogrammed Robot Masters!',
  'battle_turns' => 24,
  'battle_points' => 800,
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'wily-castle', 'field_multipliers' => array('damage' => 2.0, 'recovery' => 0.5)),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-wily',
    'player_switch' => 10,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'bass-ds', 'robot_level' => 10, 'robot_abilities' => array('buster-shot', 'bass-buster', 'energy-assault', 'recovery-breaker', 'shadow-blade', 'spark-shock', 'gemini-laser', 'search-snake')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'mega-man-ds', 'robot_level' => 7, 'robot_abilities' => array('buster-shot', 'mega-buster', 'mega-ball', 'mega-slide')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'proto-man-ds', 'robot_level' => 7, 'robot_abilities' => array('buster-shot', 'proto-buster', 'proto-shield', 'proto-strike')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'guts-man', 'robot_level' => 5, 'robot_abilities' => array('super-arm', 'attack-boost', 'defense-break', 'speed-boost')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'time-man', 'robot_level' => 5, 'robot_abilities' => array('time-arrow', 'attack-break', 'defense-boost', 'speed-boost')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 6), 'robot_token' => 'quick-man', 'robot_level' => 5, 'robot_abilities' => array('quick-boomerang', 'attack-boost', 'defense-break', 'speed-break')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 7), 'robot_token' => 'air-man', 'robot_level' => 5, 'robot_abilities' => array('air-shooter', 'attack-break', 'defense-boost', 'speed-break')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 8), 'robot_token' => 'flash-man', 'robot_level' => 5, 'robot_abilities' => array('flash-stopper', 'speed-boost', 'speed-break', 'speed-mode')),

      )
    ),
  'battle_rewards' => array(
    )
  );
?>