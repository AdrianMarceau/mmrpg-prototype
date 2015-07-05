<?
// CHILL MAN
$robot = array(
  'robot_number' => 'DWN-076',
  'robot_game' => 'MM10',
  'robot_name' => 'Chill Man',
  'robot_token' => 'chill-man',
  'robot_core' => 'freeze',
  'robot_description' => 'Glacial Study Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'explode'),
  'robot_immunities' => array('freeze'),
  'robot_abilities' => array(
  	'chill-spike',
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
        array('level' => 0, 'token' => 'chill-spike')
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