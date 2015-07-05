<?
// SUNSTAR
$robot = array(
  'robot_number' => 'SRN-00X',
  'robot_game' => 'MM30',
  'robot_name' => 'Sunstar',
  'robot_token' => 'sunstar',
  'robot_class' => 'boss',
  'robot_core' => 'flame',
  'robot_core2' => 'time',
  'robot_description' => 'Doomsday Weapon Stardroid',
  'robot_energy' => 100,
  'robot_weapons' => 30,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'nature'), // [none]
  'robot_affinities' => array('flame'),
  'robot_immunities' => array('space'),
  'robot_abilities' => array(
  	'sunstar-supernova',
    'spark-chaser', 'grab-buster', 'bubble-bomb', 'photon-missile', 'electric-shock', 'black-hole', 'deep-digger', 'salt-water', 'break-dash',
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
        array('level' => 0, 'token' => 'sunstar-supernova')
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