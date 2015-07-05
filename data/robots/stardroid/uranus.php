<?
// URANUS
$robot = array(
  'robot_number' => 'SRN-007',
  'robot_game' => 'MM30',
  'robot_name' => 'Uranus',
  'robot_token' => 'uranus',
  'robot_class' => 'boss',
  'robot_core' => 'impact',
  'robot_core2' => 'earth',
  'robot_description' => 'Charging Bull Stardroid',
  'robot_energy' => 100,
  'robot_weapons' => 20,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'impact'), // break-dash
  'robot_resistances' => array('earth'),
  'robot_immunities' => array('space'),
  'robot_abilities' => array(
  	'deep-digger',
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
        array('level' => 0, 'token' => 'deep-digger')
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