<?
// PROTOTYPE BATTLE 5 : VS DR COSSACK
$battle = array(
  'battle_name' => 'Chapter Three Rival Battle',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Cossack\'s Proto Man and Rhythm!',
  'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 2),
  'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 20 * 2),
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'cossack-citadel', 'field_name' => 'Cossack Citadel', 'field_music' => 'cossack-citadel'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-cossack',
    'player_switch' => 1.5,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'proto-man', 'robot_level' => 20, 'robot_abilities' => array('proto-buster', 'proto-shield', 'proto-strike', 'buster-shot', 'dive-torpedo', 'skull-barrier', 'bright-burst', 'drill-blitz', 'rain-flush', 'ring-boomerang', 'pharaoh-shot', 'dust-crusher')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'rhythm', 'robot_level' => 20, 'robot_abilities' => array('rhythm-buster', 'buster-shot', 'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap'))
      )
    ),
  'battle_rewards' => array(
    'robots' => array(
      ),
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 10, 'token' => 'energy-tank', 'min' => 1, 'max' => 1),
      array('chance' => 10, 'token' => 'weapon-tank', 'min' => 1, 'max' => 1),
      array('chance' => 5, 'token' => 'extra-life', 'min' => 1, 'max' => 1)
      )
    )
  );
?>