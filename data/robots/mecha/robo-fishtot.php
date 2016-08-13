<?
// ROBOT FISHTOT
$robot = array(
  'robot_number' => 'FTOT-001', // ROBOT : ROBO FISHTOT (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Robo Fishtot',
  'robot_token' => 'robo-fishtot',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Robo Fishtot (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Robo Fishtot (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'water',
  'robot_field' => 'rainy-sewers',
  'robot_description' => 'Underwater Patrol Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'electric'),
  'robot_resistances' => array('flame'),
  'robot_affinities' => array('water'),
  'robot_abilities' => array('fishtot-tackle'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'fishtot-tackle')
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