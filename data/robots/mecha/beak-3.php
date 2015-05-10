<?
// BEAK III
$robot = array(
  'robot_number' => 'BEAK-003', // ROBOT : BEAK (3rd Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Beak',
  'robot_token' => 'beak-3',
  'robot_image_editor' => 412,
  'robot_core' => 'laser',
  'robot_field' => 'orb-city',
  'robot_description' => 'Motion Sensing Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'impact'),
  'robot_resistances' => array('water', 'freeze'),
  'robot_immunities' => array('laser'),
  'robot_abilities' => array('beak-shot'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'beak-shot')
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