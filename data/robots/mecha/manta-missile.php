<?
// MANTA MISSILE
$robot = array(
  'robot_number' => 'MANT-001', // ROBOT : MANTA MISSILE (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Manta Missile',
  'robot_token' => 'manta-missile',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Manta Missile (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Manta Missile (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'missile',
  'robot_field' => 'submerged-armory',
  'robot_description' => 'Homing Mantaray Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('wind', 'space', 'shadow'),
  'robot_resistances' => array('missile', 'earth'),
  'robot_abilities' => array('manta-seeker'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'manta-seeker')
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