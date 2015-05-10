<?
// SPIN FIEND III
$robot = array(
  'robot_number' => 'SPNF-003', // ROBOT : SPIN FIEND (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Spin Fiend',
  'robot_token' => 'spin-fiend-3',
  'robot_image_editor' => 412,
  'robot_core' => 'swift',
  'robot_field' => 'spinning-greenhouse',
  'robot_description' => 'Top Dispensing Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'cutter', 'crystal'),
  'robot_resistances' => array('swift', 'electric'),
  'robot_abilities' => array('power-spin'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'power-spin')
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