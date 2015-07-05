<?
// GALAXY MAN
$robot = array(
  'robot_number' => 'DLN-072',
  'robot_game' => 'MM09',
  'robot_name' => 'Galaxy Man',
  'robot_token' => 'galaxy-man',
  'robot_image_editor' => 3842,
  'robot_core' => 'space',
  'robot_description' => 'Astronomic Calculations Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('earth', 'time'),
  'robot_resistances' => array('space', 'nature'),
  'robot_abilities' => array(
  	'galaxy-bomb',
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
        array('level' => 0, 'token' => 'galaxy-bomb')
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