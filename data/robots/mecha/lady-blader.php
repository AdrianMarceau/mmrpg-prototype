<?
// LADY BLADER
$robot = array(
  'robot_number' => 'LBLA-001', // ROBOT : LADY BLADE (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Lady Blader',
  'robot_token' => 'lady-blader',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Lady Blader (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Lady Blader (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'wind',
  'robot_field' => 'rusty-scrapheap',
  'robot_description' => 'Bamboo Copter Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('missile', 'flame'),
  'robot_resistances' => array('earth', 'nature'),
  'robot_affinities' => array('wind'),
  'robot_abilities' => array('blade-spinner'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'blade-spinner')
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