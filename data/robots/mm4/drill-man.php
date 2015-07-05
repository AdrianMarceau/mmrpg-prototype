<?
// DRILL MAN
$robot = array(
  'robot_number' => 'DCN-027',
  'robot_game' => 'MM04',
  'robot_name' => 'Drill Man',
  'robot_token' => 'drill-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Drill Man (Green Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Drill Man (Blue Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Drill Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'earth',
  'robot_field' => 'mineral-quarry',
  'robot_description' => 'Mineral Excavation Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('missile', 'freeze'), //dive-missile, ice-slasher
  'robot_resistances' => array('electric', 'cutter'),
  'robot_abilities' => array(
  	'drill-blitz',
  	'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-mode',
    'energy-boost', 'energy-break', 'repair-mode',
    'field-support', 'mecha-support',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'drill-blitz')
      )
    ),
  'robot_quotes' => array(
    'battle_start' => '',
    'battle_taunt' => '',
    'battle_victory' => '',
    'battle_defeat' => ''
    )
  );
?>