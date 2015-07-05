<?
// BURNER MAN
$robot = array(
  'robot_number' => 'KGN-005',
  'robot_game' => 'MM085',
  'robot_name' => 'Burner Man',
  'robot_token' => 'burner-man',
  'robot_core' => 'flame',
  'robot_description' => 'Rainforest Destruction Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'water'),
  'robot_resistances' => array('nature'),
  'robot_affinities' => array('flame'),
  'robot_abilities' => array(
  	'wave-burner',
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
        array('level' => 0, 'token' => 'wave-burner')
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