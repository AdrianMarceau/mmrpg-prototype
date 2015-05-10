<?
// BATTON
$robot = array(
  'robot_number' => 'BATN-001', // ROBOT : BATTON (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Batton',
  'robot_token' => 'batton',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Batton (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Batton (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'nature',
  'robot_field' => 'preserved-forest',
  'robot_description' => 'Forest Patrol Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'wind'),
  'robot_resistances' => array('earth', 'cutter', 'crystal'),
  'robot_abilities' => array('batton-drain'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'batton-drain')
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