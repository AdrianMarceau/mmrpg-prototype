<?
// FAN FIEND
$robot = array(
  'robot_number' => 'FANF-001', // ROBOT : FAN FIEND (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Fan Fiend',
  'robot_token' => 'fan-fiend',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Fan Fiend (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Fan Fiend (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'wind',
  'robot_field' => 'sky-ridge',
  'robot_description' => 'Gyrating Blades Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'nature'),
  'robot_resistances' => array('wind', 'impact'),
  'robot_abilities' => array('power-fan'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'power-fan')
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