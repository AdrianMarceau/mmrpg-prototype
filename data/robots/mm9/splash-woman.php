<?
// SPLASH MAN
$robot = array(
  'robot_number' => 'DLN-067',
  'robot_game' => 'MM09',
  'robot_name' => 'Splash Woman',
  'robot_token' => 'splash-woman',
  'robot_image_editor' => 4117,
  'robot_image_size' => 80,
  'robot_core' => 'water',
  'robot_description' => 'Aquatic Rescue Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'laser'),
  'robot_resistances' => array('earth'),
  'robot_affinities' => array('water'),
  'robot_abilities' => array(
  	'laser-trident',
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
        array('level' => 0, 'token' => 'laser-trident')
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