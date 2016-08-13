<?
// TELLY
$robot = array(
  'robot_number' => 'TELY-001', // ROBOT : TELLY (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Telly',
  'robot_token' => 'telly',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Telly (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Telly (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'flame',
  'robot_field' => 'atomic-furnace',
  'robot_description' => 'Rotating Security Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'water'),
  'robot_resistances' => array('earth', 'nature', 'wind'),
  'robot_abilities' => array('telly-burner'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'telly-burner')
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