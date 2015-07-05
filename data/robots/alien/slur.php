<?
// SLUR
$robot = array(
  'robot_number' => 'EXN-00Y',
  'robot_game' => 'MMEXE',
  'robot_name' => 'Slur',
  'robot_token' => 'slur',
  'robot_class' => 'boss',
  'robot_image_size' => 80,
  'robot_image_editor' => 412,
  'robot_core' => 'space',
  'robot_description' => 'Galactic Judge Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weapons' => 30,
  'robot_weaknesses' => array(),
  'robot_resistances' => array('space', 'freeze', 'flame'),
  'robot_affinities' => array('water', 'electric'),
  'robot_immunities' => array('copy'),
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
        array('level' => 0, 'token' => 'buster-shot'),
        //array('level' => 0, 'token' => 'space-shot'),
        //array('level' => 0, 'token' => 'space-buster'),
        //array('level' => 0, 'token' => 'space-overdrive'),
        array('level' => 30, 'token' => 'slur-aura'),
        array('level' => 60, 'token' => 'slur-twister'),
        array('level' => 90, 'token' => 'slur-supernova')
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