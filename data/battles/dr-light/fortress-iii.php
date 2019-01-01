<?
// PROTOTYPE BATTLE 5 : VS DS COPIES
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 2/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the dark soul variants of Mega Man, Bass, and Proto Man!',
  'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 3),
  'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 40 * 3),
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination-2', 'field_name' => 'Final Destination II', 'field_music' => 'final-destination-2', 'field_mechas' => array('beak-2', 'beetle-borg-2', 'tackle-fire-2', 'flea-2', 'flutter-fly-2', 'picket-man-2', 'peng-2', 'spine-2')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'mega-man-ds', 'robot_level' => 40, 'robot_item' => 'attack-booster', 'robot_abilities' => array('rolling-cutter', 'hyper-bomb', 'ice-breath', 'fire-storm', 'oil-shooter', 'thunder-strike', 'time-arrow', 'super-throw'), 'values' => array('robot_rewards' => array('robot_attack' => 300, 'robot_defense' => 300, 'robot_speed' => 300))),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'bass-ds', 'robot_level' => 40, 'robot_item' => 'speed-booster', 'robot_abilities' => array('metal-blade', 'bubble-spray', 'atomic-fire', 'leaf-shield', 'air-shooter', 'crash-bomber', 'flash-stopper', 'quick-boomerang'), 'values' => array('robot_rewards' => array('robot_attack' => 300, 'robot_defense' => 300, 'robot_speed' => 300))),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'proto-man-ds', 'robot_level' => 40, 'robot_item' => 'defense-booster', 'robot_abilities' => array('drill-blitz', 'rain-flush', 'bright-burst', 'pharaoh-shot', 'ring-boomerang', 'dust-crusher', 'skull-barrier', 'dive-torpedo'), 'values' => array('robot_rewards' => array('robot_attack' => 300, 'robot_defense' => 300, 'robot_speed' => 300)))
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      ),
    'items' => array(
      )
    )
  );
?>