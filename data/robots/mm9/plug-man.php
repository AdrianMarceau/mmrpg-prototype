<?
// PLUG MAN
$robot = array(
  'robot_number' => 'DLN-068',
  'robot_game' => 'MM09',
  'robot_name' => 'Plug Man',
  'robot_token' => 'plug-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'electric',
  'robot_description' => 'Electronic Maintenance Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('crystal', 'earth'),
  'robot_affinities' => array('electric'),
  'robot_abilities' => array(
  	'plug-ball',
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
        array('level' => 0, 'token' => 'plug-ball')
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