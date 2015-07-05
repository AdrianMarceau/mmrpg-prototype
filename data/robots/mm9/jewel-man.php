<?
// JEWEL MAN
$robot = array(
  'robot_number' => 'DLN-069',
  'robot_game' => 'MM09',
  'robot_name' => 'Jewel Man',
  'robot_token' => 'jewel-man',
  'robot_image_editor' => 110,
  'robot_core' => 'crystal',
  'robot_field' => 'gemstone-cavern',
  'robot_description' => 'Shining Diamond Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('space', 'swift'),
  'robot_resistances' => array('shadow', 'electric', 'flame'),
  'robot_abilities' => array(
  	'jewel-satellite',
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
        array('level' => 0, 'token' => 'jewel-satellite')
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