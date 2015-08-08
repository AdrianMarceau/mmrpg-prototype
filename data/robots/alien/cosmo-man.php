<?
// COSMO MAN
$robot = array(
  'robot_number' => 'EXN-00∞',
  'robot_game' => 'MMEXE',
  'robot_name' => 'Cosmo Man',
  'robot_token' => 'cosmo-man',
  'robot_class' => 'boss',
  'robot_core' => 'space',
  'robot_core2' => 'time',
  'robot_description' => 'Interstellar Command Solaroid',
  'robot_energy' => 100,
  'robot_weapons' => 90,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array(), 
  'robot_resistances' => array(),
  'robot_affinities' => array(),
  'robot_abilities' => array(
  	//'plant-barrier',
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
        array('level' => 0, 'token' => 'space-shot'),
        array('level' => 0, 'token' => 'space-buster'),
        array('level' => 0, 'token' => 'space-overdrive')
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