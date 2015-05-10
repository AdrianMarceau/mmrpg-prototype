<?
// BULB BLASTER III
$robot = array(
  'robot_number' => 'BULB-003', // ROBOT : BULB BLASTER (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Bulb Blaster',
  'robot_token' => 'bulb-blaster-3',
  'robot_image_editor' => 412,
  'robot_core' => 'electric',
  'robot_field' => 'lighting-control',
  'robot_description' => '100W Bulb Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'electric'),
  'robot_resistances' => array('freeze', 'flame'),
  'robot_immunities' => array('earth'),
  'robot_abilities' => array('flash-bulb'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'flash-bulb')
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