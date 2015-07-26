<?
// PROTOTYPE BATTLE 5 : VS ENKER
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 1/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the resurrected Enker in battle!',
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination', 'field_name' => 'Final Destination', 'field_music' => 'final-destination-enker', 'field_mechas' => array('beak', 'spine')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'enker', 'robot_level' => 35, 'robot_item' => 'item-weapon-upgrade', 'robot_abilities' => array('energy-boost', 'energy-break', 'mega-slide', 'mecha-support', 'spark-shock', 'search-snake', 'needle-cannon', 'buster-shot')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'beak', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'beak', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'beak', 'robot_level' => 30)
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      ),
    'items' => array(
      array('chance' => 10, 'token' => 'item-energy-tank'),
      array('chance' => 10, 'token' => 'item-weapon-tank'),
      array('chance' => 5, 'token' => 'item-extra-life')
      )
    ),
  'flags' => array(
    'fortress_battle' => true
    ),
  'values' => array(
    'fortress_battle_masters' => array('enker')
    )
  );
?>