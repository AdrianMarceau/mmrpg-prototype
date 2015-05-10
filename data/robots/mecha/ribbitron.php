<?
// RIBBITRON
$robot = array(
  'robot_number' => 'RBIT-001', // ROBOT : RIBBITRON (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Ribbitron',
  'robot_token' => 'ribbitron',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Ribbitron (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Ribbitron (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'impact',
  'robot_field' => 'septic-system',
  'robot_description' => 'Jumping Frog Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'cutter'),
  'robot_resistances' => array('water', 'impact'),
  'robot_abilities' => array('leap-frog'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'leap-frog')
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