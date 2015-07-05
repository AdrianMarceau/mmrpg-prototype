<?
// CACHE
$robot = array(
  'robot_number' => 'PCR-00Z',
  'robot_game' => 'MM19',
  'robot_name' => 'Cache',
  'robot_token' => 'cache',
  'robot_class' => 'boss',
  'robot_core' => 'copy',
  'robot_description' => '',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('time', 'space'),
  'robot_resistances' => array('shadow'),
  'robot_immunities' => array('copy'),
  'robot_abilities' => array(
  	/*
    'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-mode',
    'energy-boost', 'energy-break', 'repair-mode'
    */
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'copy-shot'),
        array('level' => 0, 'token' => 'copy-vision'),
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