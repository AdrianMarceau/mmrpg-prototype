<?
// SEARCH MAN
$robot = array(
  'robot_number' => 'DWN-061',
  'robot_game' => 'MM08',
  'robot_name' => 'Search Man',
  'robot_token' => 'search-man',
  'robot_core' => 'missile',
  'robot_description' => 'Jungle Commando Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'explode'),
  'robot_resistances' => array('nature', 'earth'),
  'robot_abilities' => array(
  	'homing-sniper',
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
        array('level' => 0, 'token' => 'homing-sniper')
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