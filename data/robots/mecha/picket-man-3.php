<?
// PICKET MAN III
$robot = array(
  'robot_number' => 'PCKT-003', // ROBOT : PICKET MAN (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Picket Man',
  'robot_token' => 'picket-man-3',
  'robot_image_editor' => 412,
  'robot_core' => 'cutter',
  'robot_field' => 'mountain-mines',
  'robot_description' => 'Picket Throwing Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'explode'),
  'robot_resistances' => array('water', 'flame', 'electric', 'nature'),
  'robot_abilities' => array('picket-throw'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'picket-throw')
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