<?
// DRILL MOLE
$robot = array(
  'robot_number' => 'MOLE-001', // ROBOT : DRILL MOLE (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Drill Mole',
  'robot_token' => 'drill-mole',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Drill Mole (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Drill Mole (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'earth',
  'robot_field' => 'mineral-quarry',
  'robot_description' => 'Ground Excavation Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'explode'),
  'robot_resistances' => array('wind', 'crystal', 'cutter'),
  'robot_immunities' => array('earth'),
  'robot_abilities' => array('mole-rush'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'mole-rush')
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