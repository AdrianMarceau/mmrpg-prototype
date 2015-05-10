<?
// SNAPPER
$robot = array(
  'robot_number' => 'SNAP-001', // ROBOT : SNAPPER (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Snapper',
  'robot_token' => 'snapper',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Snapper (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Snapper (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'water',
  'robot_field' => 'waterfall-institute',
  'robot_description' => 'Shifty Crab Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'explode', 'electric'),
  'robot_resistances' => array('impact', 'flame', 'freeze'),
  'robot_affinities' => array('water'),
  'robot_abilities' => array('snapper-soaker'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'snapper-soaker')
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