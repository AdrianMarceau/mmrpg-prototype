<?
// ASTRO MAN
$robot = array(
  'robot_number' => 'DWN-058',
  'robot_game' => 'MM08',
  'robot_name' => 'Astro Man',
  'robot_token' => 'astro-man',
  'robot_core' => 'space',
  'robot_description' => 'Holographic Illusions Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('missile', 'shadow'),
  'robot_resistances' => array('space', 'wind'),
  'robot_abilities' => array(
  	'astro-crush', 'copy-vision',
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
        array('level' => 0, 'token' => 'astro-crush'),
        array('level' => 2, 'token' => 'copy-vision')
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