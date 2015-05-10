<?
// ELEC'N
$robot = array(
  'robot_number' => 'ELCN-001', // ROBOT : ELEC'N (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Elec\'n',
  'robot_token' => 'elec-n',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Elec\'n (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Elec\'n (3rd Gen)', 'summons' => 60)
    ),
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