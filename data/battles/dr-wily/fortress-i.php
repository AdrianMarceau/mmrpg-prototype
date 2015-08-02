<?
// PROTOTYPE BATTLE 5 : VS DR COSSACK
$battle = array(
  'battle_name' => 'Chapter Three Rival Battle',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Cossack\'s Proto Man and Rhythm!',
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'cossack-citadel', 'field_name' => 'Cossack Citadel', 'field_music' => 'cossack-citadel'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-cossack',
    'player_switch' => 1.5,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'proto-man', 'robot_level' => 20,'robot_item' => 'item-speed-booster',  'robot_abilities' => array('proto-buster', 'proto-shield', 'proto-strike', 'buster-shot', 'dive-missile', 'skull-barrier', 'bright-burst', 'drill-blitz', 'rain-flush', 'ring-boomerang', 'pharaoh-soul', 'dust-crusher')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'rhythm', 'robot_level' => 20,'robot_item' => 'item-speed-booster',  'robot_abilities' => array('rhythm-buster', 'buster-shot', 'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap'))
      )
    ),
  'battle_rewards' => array(
    'robots' => array(
      ),
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 10, 'token' => 'item-energy-tank'),
      array('chance' => 10, 'token' => 'item-weapon-tank'),
      array('chance' => 5, 'token' => 'item-extra-life')
      )
    )
  );
?>