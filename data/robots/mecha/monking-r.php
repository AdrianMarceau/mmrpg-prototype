<?
// MONKING R
$robot = array(
  'robot_number' => 'MNKR-001', // ROBOT : MONKING R (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Monking R',
  'robot_token' => 'monking-r',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Monking R (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Monking R (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'impact',
  'robot_field' => 'rocky-plateau',
  'robot_description' => 'Returning Monkey Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'swift'),
  'robot_resistances' => array('nature', 'wind'),
  'robot_abilities' => array('monkey-punch'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'monkey-punch')
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