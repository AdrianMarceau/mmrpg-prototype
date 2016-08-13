<?
// PETIT SNAKEY
$robot = array(
  'robot_number' => 'PSNK-001', // ROBOT : PETIT SNAKEY (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM03',
  'robot_name' => 'Petit Snakey',
  'robot_token' => 'petit-snakey',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Petit Snakey (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Petit Snakey (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'nature',
  'robot_field' => 'serpent-column',
  'robot_description' => 'Small Serpent Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'cutter'),
  'robot_resistances' => array('swift', 'nature'),
  'robot_abilities' => array('snake-shot'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'snake-shot')
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