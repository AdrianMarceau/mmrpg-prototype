<?
// PROTOTYPE BATTLE 5 : VS MASTERS
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 3/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the army of powered up Robot Master copies and download their data!',
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination-3', 'field_name' => 'Final Destination III', 'field_music' => 'final-destination', 'field_mechas' => array('bulb-blaster-3', 'robo-fishtot-3', 'lady-blader-2', 'manta-missile-3', 'drill-mole-3', 'pyre-fly-3', 'ring-ring-3', 'skullmet-3')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'drill-man', 'robot_level' => 55, 'robot_abilities' => array('drill-blitz')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'toad-man', 'robot_level' => 55, 'robot_abilities' => array('rain-flush')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'bright-man', 'robot_level' => 55, 'robot_abilities' => array('bright-burst')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'pharaoh-man', 'robot_level' => 55, 'robot_abilities' => array('pharaoh-soul')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'ring-man', 'robot_level' => 55, 'robot_abilities' => array('ring-boomerang')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 6), 'robot_token' => 'dust-man', 'robot_level' => 55, 'robot_abilities' => array('dust-crusher')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 7), 'robot_token' => 'skull-man', 'robot_level' => 55, 'robot_abilities' => array('skull-barrier')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 8), 'robot_token' => 'dive-man', 'robot_level' => 55, 'robot_abilities' => array('dive-missile'))
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