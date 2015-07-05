<?
// JUNK MAN
$robot = array(
  'robot_number' => 'DWN-050',
  'robot_game' => 'MM07',
  'robot_name' => 'Junk Man',
  'robot_token' => 'junk-man',
  'robot_core' => 'earth',
  'robot_description' => 'Waste Collection Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'water'),
  'robot_resistances' => array('nature', 'flame'),
  'robot_abilities' => array(
  	'junk-shield',
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
        array('level' => 0, 'token' => 'junk-shield')
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