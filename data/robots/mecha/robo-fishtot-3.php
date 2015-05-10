<?
// ROBOT FISHTOT III
$robot = array(
  'robot_number' => 'FTOT-003', // ROBOT : ROBO FISHTOT (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Robo Fishtot',
  'robot_token' => 'robo-fishtot-3',
  'robot_image_editor' => 412,
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