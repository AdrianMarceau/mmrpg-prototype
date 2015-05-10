<?
// TACKLE FIRE
$robot = array(
  'robot_number' => 'FIRE-001', // ROBOT : TACKLE FIRE (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Tackle Fire',
  'robot_token' => 'tackle-fire',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Tackle Fire (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Tackle Fire (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'flame',
  'robot_field' => 'steel-mill',
  'robot_description' => 'Burning Spirit Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'wind', 'freeze', 'earth'),
  'robot_affinities' => array('flame'),
  'robot_immunities' => array('impact', 'cutter'),
  'robot_abilities' => array('fire-tackle'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'fire-tackle')
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