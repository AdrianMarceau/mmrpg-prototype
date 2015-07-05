<?
// COLD MAN
$robot = array(
  'robot_number' => 'KGN-002',
  'robot_game' => 'MM085',
  'robot_name' => 'Cold Man',
  'robot_token' => 'cold-man',
  'robot_core' => 'freeze',
  'robot_description' => 'Deep Freeze Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'flame'),
  'robot_resistances' => array('water'),
  'robot_affinities' => array('freeze'),
  'robot_abilities' => array(
  	'ice-wall',
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
        array('level' => 0, 'token' => 'ice-wall')
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