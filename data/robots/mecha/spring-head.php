<?
// SPRING HEAD
$robot = array(
  'robot_number' => 'SPNG-001', // ROBOT : SPRING HEAD (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Spring Head',
  'robot_token' => 'spring-head',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Spring Head (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Spring Head (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'swift',
  'robot_field' => 'underground-laboratory',
  'robot_description' => 'Spring Headbutt Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'cutter', 'water'),
  'robot_resistances' => array('impact', 'explode', 'wind'),
  'robot_abilities' => array('spring-headbutt'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'spring-headbutt')
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