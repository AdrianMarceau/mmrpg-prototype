<?
// CENTAUR MAN
$robot = array(
  'robot_number' => 'MXN-042',
  'robot_game' => 'MM06',
  'robot_name' => 'Centaur Man',
  'robot_token' => 'centaur-man',
  'robot_core' => 'time',
  'robot_description' => 'Teleporting Half-Horse Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'wind'), //knight-crush,gyro-attack
  'robot_resistances' => array('water'),
  'robot_affinities' => array('time'),
  'robot_abilities' => array(
  	'centaur-flash',
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
        array('level' => 0, 'token' => 'centaur-flash')
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