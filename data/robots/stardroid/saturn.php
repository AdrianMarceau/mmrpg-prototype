<?
// SATURN
$robot = array(
  'robot_number' => 'SRN-006',
  'robot_game' => 'MM30',
  'robot_name' => 'Saturn',
  'robot_token' => 'saturn',
  'robot_class' => 'boss',
  'robot_core' => 'shadow',
  'robot_core2' => 'impact',
  'robot_description' => 'Void Creation Stardroid',
  'robot_energy' => 100,
  'robot_weapons' => 20,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'wind'), // electric-shock
  'robot_resistances' => array('impact'),
  'robot_immunities' => array('space'),
  'robot_abilities' => array(
  	'black-hole',
  	'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
    'energy-boost', 'energy-break', 'energy-swap', 'repair-mode',
    'field-support', 'mecha-support',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'black-hole')
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