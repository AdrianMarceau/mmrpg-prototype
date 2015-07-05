<?
// JUPITER
$robot = array(
  'robot_number' => 'SRN-005',
  'robot_game' => 'MM30',
  'robot_name' => 'Jupiter',
  'robot_token' => 'jupiter',
  'robot_class' => 'boss',
  'robot_core' => 'electric',
  'robot_core2' => 'wind',
  'robot_description' => 'Electro Bombardier Stardroid',
  'robot_energy' => 100,
  'robot_weapons' => 20,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'water'), // bubble-bomb
  'robot_resistances' => array('wind'),
  'robot_immunities' => array('space'),
  'robot_abilities' => array(
  	'electric-shock',
  	'buster-shot', 'buster-charge',
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
        array('level' => 0, 'token' => 'electric-shock')
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