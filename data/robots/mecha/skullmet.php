<?
// SKULLMET
$robot = array(
  'robot_number' => 'SKMT-001', // ROBOT : SKULLMET (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM04',
  'robot_name' => 'Skullmet',
  'robot_token' => 'skullmet',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Skullmet (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Skullmet (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'shadow',
  'robot_field' => 'robosaur-boneyard',
  'robot_description' => 'Armored Skull Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'crystal'),
  'robot_resistances' => array('flame', 'impact', 'wind'),
  'robot_abilities' => array('skull-shot'),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'skull-shot')
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