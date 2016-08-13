<?
// MAG FLY
$robot = array(
  'robot_number' => 'MFLY-001', // ROBOT : MAG FLY (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Mag Fly',
  'robot_token' => 'mag-fly',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Mag Fly (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Mag Fly (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'missile',
  'robot_field' => 'magnetic-generator',
  'robot_description' => 'Flying Magnet Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'cutter', 'wind'),
  'robot_immunities' => array('earth'),
  'robot_affinities' => array('electric'),
  'robot_abilities' => array('magfly-missile'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'magfly-missile')
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