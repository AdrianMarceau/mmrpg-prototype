<?
// AIR MAN
$robot = array(
  'robot_number' => 'DWN-010',
  'robot_game' => 'MM02',
  'robot_name' => 'Air Man',
  'robot_token' => 'air-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Air Man (Red Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Air Man (Green Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Air Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'wind',
  'robot_field' => 'sky-ridge',
  'robot_description' => 'Powerful Winds Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'impact'),
  'robot_resistances' => array('water'),
  'robot_affinities' => array('wind'),
  'robot_abilities' => array(
  	'air-shooter',
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
        array('level' => 0, 'token' => 'air-shooter')
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