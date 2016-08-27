<?
// STONE MAN
$robot = array(
  'robot_number' => 'DWN-035',
  'robot_game' => 'MM05',
  'robot_name' => 'Stone Man',
  'robot_token' => 'stone-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'earth',
  'robot_description' => 'Disassembling Brick Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'freeze'), //napalm-bomb,ice-slasher
  'robot_resistances' => array('swift', 'electric'),
  'robot_abilities' => array(
  	'power-stone',
  	'buster-shot',
  	'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
    'energy-boost', 'energy-break', 'energy-swap', 'energy-mode',
    'field-support', 'mecha-support',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'power-stone')
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