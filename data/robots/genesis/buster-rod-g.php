<?
// BUSTER ROD G
$robot = array(
  'robot_number' => 'WWN-001',
  'robot_game' => 'MM21',
  'robot_name' => 'Buster Rod G',
  'robot_token' => 'buster-rod-g',
  'robot_class' => 'boss',
  'robot_core' => 'swift',
  'robot_description' => 'Agile Monkey Unit',
  'robot_energy' => 100,
  'robot_weapons' => 15,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'freeze'),
  'robot_resistances' => array('nature'),
  'robot_abilities' => array(
  	'buster-rod',
  	'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
    'energy-boost', 'energy-break', 'energy-swap', 'repair-mode',
    'field-support', 'mecha-support',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'buster-rod')
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