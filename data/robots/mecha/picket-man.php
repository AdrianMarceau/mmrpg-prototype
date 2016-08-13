<?
// PICKET MAN
$robot = array(
  'robot_number' => 'PCKT-001', // ROBOT : PICKET MAN (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Picket Man',
  'robot_token' => 'picket-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Picket Man (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Picket Man (3rd Gen)', 'summons' => 60)
    ),
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