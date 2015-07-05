<?
// BLIZZARD MAN
$robot = array(
  'robot_number' => 'MXN-041',
  'robot_game' => 'MM06',
  'robot_name' => 'Blizzard Man',
  'robot_token' => 'blizzard-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'freeze',
  'robot_description' => 'Skiing Snowball Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'explode'), //flame-blast,hyper-bomb
  'robot_resistances' => array('impact'),
  'robot_affinities' => array('freeze'),
  'robot_abilities' => array(
  	'blizzard-attack',
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
        array('level' => 0, 'token' => 'blizzard-attack')
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