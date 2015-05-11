<?
// FREEZE MAN
$robot = array(
  'robot_number' => 'DWN-049',
  'robot_game' => 'MM07',
  'robot_name' => 'Freeze Man',
  'robot_token' => 'freeze-man',
  'robot_core' => 'freeze',
  'robot_description' => 'Icy Blasts Robot',
  'robot_image_editor' => 3842,
  'robot_image_size' => 80,
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('earth', 'flame'),
  'robot_affinities' => array('freeze'),
  'robot_abilities' => array(
  	'freeze-cracker',
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
        array('level' => 0, 'token' => 'freeze-cracker')
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