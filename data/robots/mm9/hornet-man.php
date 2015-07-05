<?
// HORNET MAN
$robot = array(
  'robot_number' => 'DLN-070',
  'robot_game' => 'MM09',
  'robot_name' => 'Hornet Man',
  'robot_token' => 'hornet-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'nature',
  'robot_description' => 'Flower Pollination Robot',
  'robot_field' => 'preserved-forest',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'wind'),
  'robot_resistances' => array('earth'),
  'robot_affinities' => array('nature'),
  'robot_abilities' => array(
  	'hornet-chaser',
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
        array('level' => 0, 'token' => 'hornet-chaser')
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