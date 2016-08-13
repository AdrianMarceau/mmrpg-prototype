<?
// BOMBOMB
$robot = array(
  'robot_number' => 'BOMB-001', // ROBOT : BOMBOMB (1st Gen)
  'robot_class' => 'mecha',
  'robot_game' => 'MM01',
  'robot_name' => 'Bombomb',
  'robot_token' => 'bombomb',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Bombomb (2nd Gen)', 'summons' => 30),
    array('token' => 'alt2', 'name' => 'Bombomb (3rd Gen)', 'summons' => 60)
    ),
  'robot_core' => 'explode',
  'robot_field' => 'orb-city',
  'robot_description' => 'Explosive Demolishion Mecha',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'explode'),
  'robot_resistances' => array('impact', 'cutter'),
  //'robot_abilities' => array('bombomb-boom'),
  'robot_abilities' => array('bullet-tackle'),
  'robot_rewards' => array(
    'abilities' => array(
        //array('level' => 0, 'token' => 'bombomb-boom')
        array('level' => 0, 'token' => 'bullet-tackle')
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