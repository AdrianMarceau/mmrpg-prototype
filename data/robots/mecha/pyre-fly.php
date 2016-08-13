<?
// PYRE FLY
$robot = array(
  'robot_number' => 'PFLY-001', // ROBOT : PYRE FLY (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Pyre Fly',
  'robot_token' => 'pyre-fly',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Pyre Fly (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Pyre Fly (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'flame',
  'robot_field' => 'egyptian-excavation',
  'robot_description' => 'Burning Dragonfly Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'cutter'),
  'robot_resistances' => array('shadow', 'flame'),
  'robot_abilities' => array('pyre-flame'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'pyre-flame')
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