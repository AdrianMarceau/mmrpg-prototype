<?
// GYRO MAN
$robot = array(
  'robot_number' => 'DWN-036',
  'robot_game' => 'MM05',
  'robot_name' => 'Gyro Man',
  'robot_token' => 'gyro-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Gyro Man (Redhot Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Gyro Man (Skyblue Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Gyro Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'wind',
  'robot_description' => 'Aerial Strike Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'nature'), //gravity-hold,plant-barrier
  'robot_resistances' => array('earth', 'wind'),
  'robot_abilities' => array(
  	'gyro-attack',
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
        array('level' => 0, 'token' => 'gyro-attack')
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