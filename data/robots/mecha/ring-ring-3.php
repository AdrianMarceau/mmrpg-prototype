<?
// RING RING III
$robot = array(
  'robot_number' => 'RING-003', // ROBOT : RING RING (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Ring Ring',
  'robot_token' => 'ring-ring-3',
  'robot_image_editor' => 412,
  'robot_core' => 'space',
  'robot_field' => 'space-simulator',
  'robot_description' => 'Saturn Seeking Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'impact'),
  'robot_resistances' => array('water', 'freeze'),
  'robot_immunities' => array('laser'),
  'robot_abilities' => array('ring-tackle'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'ring-tackle')
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