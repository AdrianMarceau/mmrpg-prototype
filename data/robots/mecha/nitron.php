<?
// NITRON
$robot = array(
  'robot_number' => 'NTRN-001', // ROBOT : NITRON (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Nitron',
  'robot_token' => 'nitron',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Nitron (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Nitron (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'flame',
  'robot_field' => 'reflection-chamber',
  'robot_description' => 'Flaming Geysers Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'electric'),
  'robot_affinities' => array('flame'),
  'robot_abilities' => array('nitron-fire'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'nitron-fire'),
        array('level' => 0, 'token' => 'nitron-bolt'),
        array('level' => 0, 'token' => 'nitron-ice')
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