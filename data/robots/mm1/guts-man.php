<?
// GUTS MAN
$robot = array(
  'robot_number' => 'DLN-004',
  'robot_game' => 'MM01',
  'robot_name' => 'Guts Man',
  'robot_token' => 'guts-man',
  'robot_image_editor' => 412,
  'robot_core' => 'impact',
  'robot_description' => 'Tough Construction Robot',
  'robot_field' => 'mountain-mines',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'time'),
  'robot_resistances' => array('impact'),
  'robot_abilities' => array(
  	'super-throw', 'super-arm',
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
        array('level' => 0, 'token' => 'super-throw'),
        array('level' => 6, 'token' => 'super-arm')
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