<?
// CLOUD MAN
$robot = array(
  'robot_number' => 'DWN-052',
  'robot_game' => 'MM07',
  'robot_name' => 'Cloud Man',
  'robot_token' => 'cloud-man',
  'robot_core' => 'electric',
  'robot_description' => 'Hovering Cloud Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'wind'),
  'robot_affinities' => array('electric'),
  'robot_immunities' => array('earth'),
  'robot_abilities' => array(
  	'thunder-bolt',
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
        array('level' => 0, 'token' => 'thunder-bolt')
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