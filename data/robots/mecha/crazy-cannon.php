<?
// CRAZY CANNON
$robot = array(
  'robot_number' => 'CRAZ-001', // ROBOT : CRAZY CANNON (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM02',
  'robot_name' => 'Crazy Cannon',
  'robot_token' => 'crazy-cannon',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Crazy Cannon (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Crazy Cannon (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'time',
  'robot_field' => 'photon-collider',
  'robot_description' => 'Tachyon Cannon Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'earth'),
  'robot_resistances' => array('space', 'swift'),
  'robot_immunities' => array('time'),
  'robot_abilities' => array('cannon-shot'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'cannon-shot')
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