<?
// NEEDLE NED
$robot = array(
  'robot_number' => 'NLND-001', // ROBOT : NEEDLE NED (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Needle Ned',
  'robot_token' => 'needle-ned',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Needle Ned (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Needle Ned (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'cutter',
  'robot_field' => 'construction-site',
  'robot_description' => 'Spikey Porcupine Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('crystal', 'laser', 'swift'),
  'robot_resistances' => array('impact', 'explode'),
  'robot_abilities' => array('pins-needles'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'pins-needles')
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