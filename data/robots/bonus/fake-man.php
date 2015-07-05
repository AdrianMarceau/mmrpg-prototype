<?
// FAKE MAN
$robot = array(
  'robot_number' => 'RPG-00A', // Prototype RPG
  'robot_game' => 'MMRPG',
  'robot_name' => 'Fake Man',
  'robot_token' => 'fake-man',
  'robot_description' => '',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric'),
  'robot_resistances' => array('cutter', 'explode', 'missile', 'flame'),
  'robot_abilities' => array(
  	'buster-shot', 'buster-charge', 'revolver-buster',
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
        array('level' => 0, 'token' => 'revolver-buster')
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