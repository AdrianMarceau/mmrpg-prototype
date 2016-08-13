<?
// PIERROBOT
$robot = array(
  'robot_number' => 'PIER-001', // ROBOT : PIERROBOT (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Pierrobot',
  'robot_token' => 'pierrobot',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Pierrobot (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Pierrobot (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'cutter',
  'robot_field' => 'industrial-facility',
  'robot_description' => 'Balancing Performer Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'nature'),
  'robot_resistances' => array('freeze'),
  'robot_immunities' => array('water'),
  'robot_abilities' => array('pierro-gear'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'pierro-gear')
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