<?
// TELLY III
$robot = array(
  'robot_number' => 'TELY-003', // ROBOT : TELLY III (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Telly',
  'robot_token' => 'telly-3',
  'robot_image_editor' => 412,
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