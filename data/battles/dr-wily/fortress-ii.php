<?
// PROTOTYPE BATTLE 5 : VS PUNK
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 1/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the resurrected Punk in battle!',
  'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 1),
  'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 40 * 1),
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination', 'field_name' => 'Final Destination', 'field_music' => 'final-destination-punk', 'field_mechas' => array('batton', 'crazy-cannon', 'fan-fiend', 'killer-bullet', 'pierrobot', 'snapper', 'spring-head', 'telly')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'punk', 'robot_level' => 40, 'robot_abilities' => array('energy-boost', 'energy-break', 'bass-baroque', 'mecha-support', 'top-spin', 'shadow-blade', 'hard-knuckle', 'buster-shot'))
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 20, 'token' => 'energy-tank'),
      array('chance' => 20, 'token' => 'weapon-tank'),
      array('chance' => 10, 'token' => 'extra-life')
      )
    )
  );
?>