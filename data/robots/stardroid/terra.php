<?
// TERRA
$robot = array(
  'robot_number' => 'SRN-001',
  'robot_game' => 'MM30',
  'robot_name' => 'Terra',
  'robot_token' => 'terra',
  'robot_class' => 'boss',
  'robot_core' => 'laser',
  'robot_core2' => 'electric',
  'robot_description' => 'Homing Lasers Stardroid',
  'robot_energy' => 100,
  'robot_weapons' => 20,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'earth'), // deep-digger
  'robot_resistances' => array('electric'),
  'robot_immunities' => array('space'),
  'robot_abilities' => array(
  	'spark-chaser',
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
        array('level' => 0, 'token' => 'spark-chaser')
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