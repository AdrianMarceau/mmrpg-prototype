<?
// PROTOTYPE BATTLE 5 : VS DARKNESS MASTERS
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 3/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the army of robot master clones augmented with darkness energy!',
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination-3', 'field_name' => 'Final Destination III', 'field_music' => 'final-destination', 'field_mechas' => array('beak-3', 'beetle-borg-3', 'tackle-fire-3', 'flea-3', 'flutter-fly-3', 'picket-man-3', 'peng-3', 'spine-3')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'robot', 'robot_level' => 40, 'robot_abilities' => array('buster-shot'))
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