<?
// KING
$robot = array(
  'robot_number' => 'KGN-00X',
  'robot_game' => 'MM085',
  'robot_name' => 'King',
  'robot_token' => 'king',
  'robot_class' => 'boss',
  'robot_core' => 'shield',
  'robot_description' => 'Rebel Leader Robot',
  'robot_energy' => 100,
  'robot_weapons' => 20,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'cutter'),
  'robot_resistances' => array('impact', 'explode'),
  'robot_abilities' => array(
  	'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-mode',
    'energy-boost', 'energy-break', 'repair-mode',
    'field-support', 'mecha-support',
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        //array('level' => 0, 'token' => 'remote-mine')
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