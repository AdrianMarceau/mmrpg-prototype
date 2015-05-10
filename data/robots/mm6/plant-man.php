<?
// PLANT MAN
$robot = array(
  'robot_number' => 'MXN-045',
  'robot_game' => 'MM06',
  'robot_name' => 'Plant Man',
  'robot_token' => 'plant-man',
  'robot_core' => 'nature',
  'robot_description' => 'Flora Analysis Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'flame'), //blizzard-attack,[gyro-attack],atomic-fire
  'robot_resistances' => array('nature'),
  'robot_affinities' => array('water'),
  'robot_abilities' => array(
  	'plant-barrier',
  	'buster-shot',
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
        array('level' => 0, 'token' => 'plant-barrier')
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