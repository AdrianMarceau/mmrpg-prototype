<?
// ELEC'N III
$robot = array(
  'robot_number' => 'ELCN-003', // ROBOT : ELEC'N (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Elec\'n',
  'robot_token' => 'elec-n-3',
  'robot_image_editor' => 412,
  'robot_core' => 'electric',
  'robot_field' => 'power-plant',
  'robot_description' => 'Flying Plug Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'cutter', 'crystal'),
  'robot_immunities' => array('swift'),
  'robot_affinities' => array('electric'),
  'robot_abilities' => array('elec-spark'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'elec-spark')
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