<?
// MAGNET MAN
$robot = array(
  'robot_number' => 'DWN-018',
  'robot_game' => 'MM03',
  'robot_name' => 'Magnet Man',
  'robot_token' => 'magnet-man',
  'robot_image_editor' => 110,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Magnet Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Magnet Man (Gold Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Magnet Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'missile',
  'robot_field' => 'magnetic-generator',
  'robot_description' => 'Electro Magnetism Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'cutter'), //spark-shock
  'robot_resistances' => array('missile'),
  'robot_abilities' => array(
  	'magnet-missile',
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
        array('level' => 0, 'token' => 'magnet-missile')
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