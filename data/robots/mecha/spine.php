<?
// SPINE
$robot = array(
  'robot_number' => 'SPNE-001', // ROBOT : SPINE (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Spine',
  'robot_token' => 'spine',
  'robot_image_editor' => 412,
  'robot_core' => 'electric',
  'robot_field' => 'electrical-tower',
  'robot_description' => 'Grounded Patrol Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('earth', 'water'),
  'robot_resistances' => array('explode', 'impact', 'cutter'),
  'robot_affinities' => array('electric'),
  'robot_abilities' => array('spine-slide'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'spine-slide')
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