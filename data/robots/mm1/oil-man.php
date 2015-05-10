<?
// OIL MAN
$robot = array(
  'robot_number' => 'DLN-00B',
  'robot_game' => 'MM01',
  'robot_name' => 'Oil Man',
  'robot_token' => 'oil-man',
  'robot_image_editor' => 412,
  'robot_core' => 'earth',
  'robot_description' => 'Slick Sliding Robot',
  'robot_field' => 'oil-wells',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'freeze'),
  'robot_resistances' => array('water', 'electric'),
  'robot_abilities' => array(
  	'oil-shooter', 'oil-slider',
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
        array('level' => 0, 'token' => 'oil-shooter'),
        array('level' => 6, 'token' => 'oil-slider')
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