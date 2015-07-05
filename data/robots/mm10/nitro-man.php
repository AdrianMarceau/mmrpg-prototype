<?
// NITRO MAN
$robot = array(
  'robot_number' => 'DWN-079',
  'robot_game' => 'MM10',
  'robot_name' => 'Nitro Man',
  'robot_token' => 'nitro-man',
  'robot_core' => 'swift',
  'robot_description' => 'Motorcycle Stunt Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'earth'),
  'robot_resistances' => array('missile', 'impact'),
  'robot_abilities' => array(
  	'wheel-cutter',
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
        array('level' => 0, 'token' => 'wheel-cutter')
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