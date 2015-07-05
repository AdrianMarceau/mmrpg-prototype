<?
// WIND MAN
$robot = array(
  'robot_number' => 'MXN-047',
  'robot_game' => 'MM06',
  'robot_name' => 'Wind Man',
  'robot_token' => 'wind-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'wind',
  'robot_description' => 'Flying Gusts Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('time', 'space'), //centaur-flash[why?],gemini-laser[w/e]
  'robot_resistances' => array('earth'),
  'robot_affinities' => array('wind'),
  'robot_abilities' => array(
  	'wind-storm',
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
        array('level' => 0, 'token' => 'wind-storm')
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