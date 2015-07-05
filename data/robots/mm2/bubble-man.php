<?
// BUBBLE MAN
$robot = array(
  'robot_number' => 'DWN-011',
  'robot_game' => 'MM02',
  'robot_name' => 'Bubble Man',
  'robot_token' => 'bubble-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Bubble Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Bubble Man (Red Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Bubble Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'water',
  'robot_description' => 'Underwater Combat Robot',
  'robot_field' => 'waterfall-institute',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'electric'),
  'robot_resistances' => array('flame'),
  'robot_immunities' => array('water'),
  'robot_abilities' => array(
  	'bubble-lead', 'bubble-spray',
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
        array('level' => 0, 'token' => 'bubble-spray'),
        array('level' => 10, 'token' => 'bubble-lead')
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