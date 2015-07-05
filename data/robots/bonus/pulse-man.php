<?
// PULSE MAN
$robot = array(
  'robot_number' => 'RPG-00C', // Prototype RPG
  'robot_game' => 'MMRPG',
  'robot_name' => 'Pulse Man',
  'robot_token' => 'pulse-man',
  'robot_description' => 'Electrical Communication Robot',
  'robot_image_editor' => 9999, // non-member
  'robot_core' => 'electric',
  'robot_field' => 'intro-field',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water'),
  'robot_resistances' => array('swift', 'shield', 'laser'),
  'robot_affinities' => array('electric'),
  'robot_abilities' => array(
  	'buster-shot', 'buster-charge', 'volt-tackle',
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
        array('level' => 0, 'token' => 'volt-tackle')
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