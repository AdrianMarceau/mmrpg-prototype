<?
// KNIGHT MAN
$robot = array(
  'robot_number' => 'MXN-044',
  'robot_game' => 'MM06',
  'robot_name' => 'Knight Man',
  'robot_token' => 'knight-man',
  'robot_image_editor' => 18,
  'robot_image_size' => 80,
  'robot_core' => 'impact',
  'robot_description' => 'Chivalrous Knight Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'shadow'), //yamato-spear,shadow-blade
  'robot_resistances' => array('missile', 'shield'),
  'robot_abilities' => array(
  	'knight-crush',
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
        array('level' => 0, 'token' => 'knight-crush')
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