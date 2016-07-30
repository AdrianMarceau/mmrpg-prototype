<?
// PROTOTYPE BATTLE 5 : VS COPIES
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 2/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the powered up copies of Proto Man, Mega Man, and Bass!',
  'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 3),
  'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 50 * 3),
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination-2', 'field_name' => 'Final Destination II', 'field_music' => 'final-destination-2', 'field_mechas' => array('bulb-blaster-2', 'robo-fishtot-2', 'lady-blader-2', 'manta-missile-2', 'drill-mole-2', 'pyre-fly-2', 'ring-ring-2', 'skullmet-2')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'proto-man-copy', 'robot_level' => 50, 'robot_abilities' => array('drill-blitz', 'rain-flush', 'bright-burst', 'pharaoh-shot', 'ring-boomerang', 'dust-crusher', 'skull-barrier', 'dive-torpedo')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'mega-man-copy', 'robot_level' => 50, 'robot_abilities' => array('rolling-cutter', 'hyper-bomb', 'ice-breath', 'fire-storm', 'oil-shooter', 'thunder-strike', 'time-arrow', 'super-throw')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'bass-copy', 'robot_level' => 50, 'robot_abilities' => array('metal-blade', 'bubble-spray', 'atomic-fire', 'leaf-shield', 'air-shooter', 'crash-bomber', 'flash-stopper', 'quick-boomerang'))
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 40, 'token' => 'energy-tank'),
      array('chance' => 40, 'token' => 'weapon-tank'),
      array('chance' => 20, 'token' => 'extra-life')
      )
    )
  );
?>