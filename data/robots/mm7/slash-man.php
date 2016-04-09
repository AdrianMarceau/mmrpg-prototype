<?
// SLASH MAN
$robot = array(
  'robot_number' => 'DWN-054',
  'robot_game' => 'MM07',
  'robot_name' => 'Slash Man',
  'robot_token' => 'slash-man',
  'robot_image_editor' => 3842,
  'robot_image_size' => 80,
  'robot_core' => 'nature',
  'robot_description' => 'Razor Claws Robot2',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'flame'),
  'robot_immunities' => array('swift'),
  'robot_abilities' => array(
  	'slash-claw',
  	'buster-shot',
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
        array('level' => 0, 'token' => 'slash-claw')
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