<?
// SPLASH MAN
$robot = array(
  'robot_number' => 'DLN-067',
  'robot_game' => 'MM09',
  'robot_name' => 'Splash Woman',
  'robot_token' => 'splash-woman',
  'robot_gender' => 'female',
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