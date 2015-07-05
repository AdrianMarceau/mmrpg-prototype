<?
// STRIKE MAN
$robot = array(
  'robot_number' => 'DWN-078',
  'robot_game' => 'MM10',
  'robot_name' => 'Strike Man',
  'robot_token' => 'strike-man',
  'robot_image_editor' => 3842,
  'robot_image_size' => 80,
  'robot_core' => 'impact',
  'robot_description' => 'Baseball Pitching Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'crystal'),
  'robot_resistances' => array('electric', 'impact'),
  'robot_abilities' => array(
  	'rebound-striker',
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
        array('level' => 0, 'token' => 'rebound-striker')
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