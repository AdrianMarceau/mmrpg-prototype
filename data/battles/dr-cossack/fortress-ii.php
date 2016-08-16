<?
// PROTOTYPE BATTLE 5 : VS BALLADE
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 1/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the resurrected Ballade in battle!',
  'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 1),
  'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 45 * 1),
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination', 'field_name' => 'Final Destination', 'field_music' => 'final-destination-ballade', 'field_mechas' => array('bulb-blaster', 'robo-fishtot', 'lady-blader', 'manta-missile', 'drill-mole', 'pyre-fly', 'ring-ring', 'skullmet')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'ballade', 'robot_level' => 45, 'robot_abilities' => array('energy-boost', 'energy-break', 'proto-strike', 'mecha-support', 'gemini-laser', 'magnet-missile', 'spark-shock', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'beak', 'robot_image' => 'beak_alt2', 'robot_level' => 40, 'robot_abilities' => array('beak-shot', 'attack-boost', 'defense-boost', 'speed-boost'), 'flags' => array('hide_from_mission_select' => true)),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'beak', 'robot_image' => 'beak_alt2', 'robot_level' => 40, 'robot_abilities' => array('beak-shot', 'attack-break', 'defense-break', 'speed-break'), 'flags' => array('hide_from_mission_select' => true)),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'beak', 'robot_image' => 'beak_alt2', 'robot_level' => 40, 'robot_abilities' => array('beak-shot', 'attack-boost', 'defense-boost', 'speed-boost'), 'flags' => array('hide_from_mission_select' => true)),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'beak', 'robot_image' => 'beak_alt2', 'robot_level' => 40, 'robot_abilities' => array('beak-shot', 'attack-break', 'defense-break', 'speed-break'), 'flags' => array('hide_from_mission_select' => true))
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 20, 'token' => 'energy-tank', 'min' => 1, 'max' => 2),
      array('chance' => 20, 'token' => 'weapon-tank', 'min' => 1, 'max' => 2),
      array('chance' => 10, 'token' => 'extra-life', 'min' => 1, 'max' => 2)
      )
    )
  );
?>