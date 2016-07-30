<?
// PROTOTYPE BATTLE 5 : VS DR LIGHT
$battle = array(
  'battle_name' => 'Chapter Three Rival Battle',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Light\'s Mega Man and Roll!',
  'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 2),
  'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 30 * 2),
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'light-laboratory', 'field_name' => 'Light Laboratory', 'field_music' => 'light-laboratory'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-light',
    'player_switch' => 1.5,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'mega-man', 'robot_level' => 30, 'robot_abilities' => array('mega-buster', 'mega-ball', 'mega-slide', 'buster-shot', 'rolling-cutter', 'super-throw', 'time-arrow', 'thunder-strike', 'oil-shooter', 'fire-storm', 'ice-breath', 'hyper-bomb')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'roll', 'robot_level' => 30, 'robot_abilities' => array('roll-buster', 'buster-shot', 'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost'))
      )
    ),
  'battle_rewards' => array(
    'robots' => array(
      ),
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 10, 'token' => 'energy-tank'),
      array('chance' => 10, 'token' => 'weapon-tank'),
      array('chance' => 5, 'token' => 'extra-life')
      )
    )
  );
?>