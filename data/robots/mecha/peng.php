<?
// PENG
$robot = array(
  'robot_number' => 'PENG-001', // ROBOT : PENG (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Peng',
  'robot_token' => 'peng',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Peng (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Peng (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'freeze',
  'robot_field' => 'arctic-jungle',
  'robot_description' => 'Sliding Penguin Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'nature'),
  'robot_resistances' => array('flame'),
  'robot_affinities' => array('water'),
  'robot_immunities' => array('freeze'),
  'robot_abilities' => array('peng-slide'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'peng-slide')
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