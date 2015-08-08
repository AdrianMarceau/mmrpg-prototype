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
  'robot_weaknesses' => array('freeze', 'flame'), 
  'robot_resistances' => array('nature'),
  'robot_affinities' => array('water'),
  'robot_abilities' => array(
  	'plant-barrier',
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