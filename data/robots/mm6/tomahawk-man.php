<?
// TOMAHAWK MAN
$robot = array(
  'robot_number' => 'MXN-046',
  'robot_game' => 'MM06',
  'robot_name' => 'Tomahawk Man',
  'robot_token' => 'tomahawk-man',
  'robot_image_editor' => 5161,
  'robot_image_size' => 80,
  'robot_core' => 'earth',
  'robot_description' => 'Brave Warrior Robot',
  'robot_image_editor' => 5161,
  'robot_image_size' => 80,
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'space'), //plant-barrier,star-crash
  'robot_resistances' => array('cutter'),
  'robot_abilities' => array(
  	'silver-tomahawk',
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
        array('level' => 0, 'token' => 'silver-tomahawk')
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