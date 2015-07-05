<?
// TURBO MAN
$robot = array(
  'robot_number' => 'DWN-056',
  'robot_game' => 'MM07',
  'robot_name' => 'Turbo Man',
  'robot_token' => 'turbo-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'swift',
  'robot_description' => 'Transforming Auto Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('shadow', 'earth'),
  'robot_resistances' => array('swift'),
  'robot_immunities' => array('electric'),
  'robot_abilities' => array(
  	'scorch-wheel',
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
        array('level' => 0, 'token' => 'scorch-wheel')
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