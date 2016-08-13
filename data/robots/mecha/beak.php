<?
// BEAK
$robot = array(
  'robot_number' => 'BEAK-001', // ROBOT : BEAK (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM005',
  'robot_name' => 'Beak',
  'robot_token' => 'beak',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Beak (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Beak (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'laser',
  'robot_field' => 'final-destination',
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