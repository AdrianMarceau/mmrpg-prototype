<?
// SHIELD MAN
$robot = array(
  'robot_number' => 'PCR-008',
  'robot_game' => 'MM19',
  'robot_name' => 'Shield Man',
  'robot_token' => 'shield-man',
  'robot_core' => 'shield',
  'robot_description' => 'Defensive Barriers Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array(),
  'robot_resistances' => array(),
  'robot_affinities' => array(),
  'robot_abilities' => array(
  	//'plant-barrier',
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
        //array('level' => 0, 'token' => 'plant-barrier')
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