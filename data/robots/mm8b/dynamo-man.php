<?
// DYNAMO MAN
$robot = array(
  'robot_number' => 'KGN-001',
  'robot_game' => 'MM085',
  'robot_name' => 'Dynamo Man',
  'robot_token' => 'dynamo-man',
  'robot_core' => 'electric',
  'robot_description' => 'Power-Plant Supervisor Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('space', 'shadow'),
  'robot_resistances' => array('wind'),
  'robot_affinities' => array('electric'),
  'robot_abilities' => array(
  	'lightning-bolt',
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
        array('level' => 0, 'token' => 'lightning-bolt')
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