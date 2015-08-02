<?
// PROTOTYPE BATTLE 5 : VS DR WILY
$battle = array(
  'battle_name' => 'Chapter Three Rival Battle',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat Dr. Wily\'s Bass and Disco!',
  'battle_turns' => 1,
  'battle_points' => 1,
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'wily-castle', 'field_name' => 'Wily Castle', 'field_music' => 'wily-castle'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'dr-wily',
    'player_switch' => 1.5,
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'bass', 'robot_level' => 1, 'robot_item' => 'item-attack-booster', 'robot_abilities' => array('bass-buster', 'bass-crush', 'bass-baroque', 'buster-shot', 'leaf-shield', 'atomic-fire', 'bubble-lead', 'metal-blade', 'quick-boomerang', 'flash-stopper', 'crash-bomber', 'air-shooter')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'disco', 'robot_level' => 1, 'robot_item' => 'item-attack-booster', 'robot_abilities' => array('disco-buster', 'buster-shot', 'attack-break', 'defense-break', 'speed-break', 'energy-break'))
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