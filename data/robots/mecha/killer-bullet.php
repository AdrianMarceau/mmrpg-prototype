<?
// KILLER BULLET
$robot = array(
  'robot_number' => 'KBUL-001', // ROBOT : KILLER BULLET (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Killer Bullet',
  'robot_token' => 'killer-bullet',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Killer Bullet (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Killer Bullet (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'explode',
  'robot_field' => 'pipe-station',
  'robot_description' => 'Industrial Bomb Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'flame'),
  'robot_resistances' => array('earth', 'water'),
  'robot_abilities' => array('bullet-tackle'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'bullet-tackle')
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