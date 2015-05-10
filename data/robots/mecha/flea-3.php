<?
// FLEA III
$robot = array(
  'robot_number' => 'FLEA-003', // ROBOT : FLEA (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Flea',
  'robot_token' => 'flea-3',
  'robot_image_editor' => 412,
  'robot_core' => 'impact',
  'robot_field' => 'abandoned-warehouse',
  'robot_description' => 'Jumping Cricket Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'electric'),
  'robot_resistances' => array('missile', 'impact'),
  'robot_abilities' => array('flea-jump'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'flea-jump')
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