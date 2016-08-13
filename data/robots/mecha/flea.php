<?
// FLEA
$robot = array(
  'robot_number' => 'FLEA-001', // ROBOT : FLEA (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Flea',
  'robot_token' => 'flea',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Flea (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Flea (3rd Gen)', 'summons' => 60)
    ),
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