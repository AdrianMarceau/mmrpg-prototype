<?
// MEGA MAN
$robot = array(
  'robot_number' => 'DLN-001',
  'robot_game' => 'MM00',
  'robot_name' => 'Rock',
  'robot_token' => 'rock',
  'robot_description' => 'Lab Assistant Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'wind'),
  'robot_resistances' => array('shadow'),
  'robot_abilities' => array(
  	'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-mode',
    'energy-boost', 'energy-break', 'repair-mode',
    'field-support', 'mecha-support'
    ),
  'robot_rewards' => array(
    'abilities' => array(
      array('level' => 0, 'token' => 'buster-shot')
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