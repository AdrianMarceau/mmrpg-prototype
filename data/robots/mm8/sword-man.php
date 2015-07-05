<?
// SWORD MAN
$robot = array(
  'robot_number' => 'DWN-059',
  'robot_game' => 'MM08',
  'robot_name' => 'Sword Man',
  'robot_token' => 'sword-man',
  'robot_core' => 'cutter',
  'robot_description' => 'Noble Swordsman Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'earth'),
  'robot_immunities' => array('flame'),
  'robot_abilities' => array(
  	'flame-sword',
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
        array('level' => 0, 'token' => 'flame-sword')
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