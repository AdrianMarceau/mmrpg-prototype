<?
// SHADE MAN
$robot = array(
  'robot_number' => 'DWN-055',
  'robot_game' => 'MM07',
  'robot_name' => 'Shade Man',
  'robot_token' => 'shade-man',
  'robot_core' => 'shadow',
  'robot_description' => 'Oil-Sucking Vampire Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'cutter'),
  'robot_resistances' => array('earth', 'shadow'),
  'robot_abilities' => array(
  	'noise-crush',
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
        array('level' => 0, 'token' => 'noise-crush')
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