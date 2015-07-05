<?
// HYPER STORM H
$robot = array(
  'robot_number' => 'WWN-003',
  'robot_game' => 'MM21',
  'robot_name' => 'Hyper Storm H',
  'robot_token' => 'hyper-storm-h',
  'robot_class' => 'boss',
  'robot_core' => 'wind',
  'robot_description' => 'Bulky Boar Unit',
  'robot_energy' => 100,
  'robot_weapons' => 15,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'explode'),
  'robot_resistances' => array('impact'),
  'robot_abilities' => array(
  	'hyper-storm',
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
        array('level' => 0, 'token' => 'hyper-storm')
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