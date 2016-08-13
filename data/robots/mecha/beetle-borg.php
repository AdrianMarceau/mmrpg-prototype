<?
// BEETLE BORG
$robot = array(
  'robot_number' => 'BTLB-001', // ROBOT : BEETLE BORG (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Beetle Borg',
  'robot_token' => 'beetle-borg',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Beetle (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Beetle (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'earth',
  'robot_field' => 'oil-wells',
  'robot_description' => 'Earth Rolling Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'crystal'),
  'robot_resistances' => array('earth', 'wind', 'impact', 'explode'),
  'robot_abilities' => array('beetle-ball'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'beetle-ball')
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