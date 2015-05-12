<?
// PROTOTYPE BATTLE 5 : VS MASTERS
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 3/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the army of powered up Robot Master copies and download their data!',
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination-3', 'field_name' => 'Final Destination III', 'field_music' => 'final-destination', 'field_mechas' => array('beak-3', 'beetle-borg-3', 'tackle-fire-3', 'flea-3', 'flutter-fly-3', 'picket-man-3', 'peng-3', 'spine-3')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'cut-man', 'robot_level' => 45, 'robot_abilities' => array('rolling-cutter')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'ice-man', 'robot_level' => 45, 'robot_abilities' => array('ice-slasher')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'fire-man', 'robot_level' => 45, 'robot_abilities' => array('fire-storm', 'fire-chaser')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'elec-man', 'robot_level' => 45, 'robot_abilities' => array('thunder-beam')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'oil-man', 'robot_level' => 45, 'robot_abilities' => array('oil-shooter', 'oil-slider')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 6), 'robot_token' => 'time-man', 'robot_level' => 45, 'robot_abilities' => array('time-arrow')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 7), 'robot_token' => 'guts-man', 'robot_level' => 45, 'robot_abilities' => array('super-arm', 'super-throw')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 8), 'robot_token' => 'bomb-man', 'robot_level' => 45, 'robot_abilities' => array('hyper-bomb'))
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 80, 'token' => 'item-energy-tank'),
      array('chance' => 80, 'token' => 'item-weapon-tank'),
      array('chance' => 40, 'token' => 'item-extra-life')
      )
    )
  );
?>