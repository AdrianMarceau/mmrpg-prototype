<?
// TENGU MAN
$robot = array(
  'robot_number' => 'DWN-057',
  'robot_game' => 'MM08',
  'robot_name' => 'Tengu Man',
  'robot_token' => 'tengu-man',
  'robot_core' => 'wind',
  'robot_description' => 'Jet-Powered Flight Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'missile'),
  'robot_immunities' => array('earth'),
  'robot_abilities' => array(
  	'tornado-hold', 'tengu-blade',
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
        array('level' => 0, 'token' => 'tornado-hold'),
        array('level' => 2, 'token' => 'tengu-blade')
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