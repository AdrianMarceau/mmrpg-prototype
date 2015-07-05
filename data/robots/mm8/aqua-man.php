<?
// AQUA MAN
$robot = array(
  'robot_number' => 'DWN-064',
  'robot_game' => 'MM08',
  'robot_name' => 'Aqua Man',
  'robot_token' => 'aqua-man',
  'robot_core' => 'water',
  'robot_field' => 'waterfall-institute',
  'robot_description' => 'Water Spraying Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('space', 'electric'),
  'robot_affinity' => array('water'),
  'robot_abilities' => array(
  	'water-balloon',
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
        array('level' => 0, 'token' => 'water-balloon')
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