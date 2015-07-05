<?
// COMMANDO MAN
$robot = array(
  'robot_number' => 'DWN-075',
  'robot_game' => 'MM10',
  'robot_name' => 'Commando Man',
  'robot_token' => 'commando-man',
  'robot_core' => 'missile',
  'robot_description' => 'Minesweeper Tank Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'electric'),
  'robot_resistances' => array('impact', 'earth'),
  'robot_abilities' => array(
  	'commando-bomb',
  	'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-mode',
    'energy-boost', 'energy-break', 'repair-mode',
    'field-support', 'mecha-support',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'commando-bomb')
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