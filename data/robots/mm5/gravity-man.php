<?
// GRAVITY MAN
$robot = array(
  'robot_number' => 'DWN-033',
  'robot_game' => 'MM05',
  'robot_name' => 'Gravity Man',
  'robot_token' => 'gravity-man',
  'robot_image_editor' => 18,
  'robot_image_size' => 80,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Gravity Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Gravity Man (Purple Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Gravity Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'electric',
  'robot_description' => 'Gravity Manipulation Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('space', 'shadow'), //star-crash,shadow-blade
  'robot_resistances' => array('wind'),
  'robot_abilities' => array(
  	'gravity-hold',
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
        array('level' => 0, 'token' => 'gravity-hold')
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