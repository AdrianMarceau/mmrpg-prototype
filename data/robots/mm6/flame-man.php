<?
// FLAME MAN
$robot = array(
  'robot_number' => 'MXN-043',
  'robot_game' => 'MM06',
  'robot_name' => 'Flame Man',
  'robot_token' => 'flame-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'flame',
  'robot_description' => 'Refinery Control Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('wind', 'water'), //wind-storm,bubble-lead
  'robot_resistances' => array('earth'),
  'robot_affinities' => array('flame'),
  'robot_abilities' => array(
  	'flame-blast',
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
        array('level' => 0, 'token' => 'flame-blast')
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