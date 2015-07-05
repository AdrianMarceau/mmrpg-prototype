<?
// FIRE MAN
$robot = array(
  'robot_number' => 'DLN-007',
  'robot_game' => 'MM01',
  'robot_name' => 'Fire Man',
  'robot_token' => 'fire-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Fire Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Fire Man (Yellow Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Fire Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'flame',
  'robot_description' => 'Trash Incinerator Robot',
  'robot_field' => 'steel-mill',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('freeze', 'wind'),
  'robot_resistances' => array('water'),
  'robot_affinities' => array('flame'),
  'robot_abilities' => array(
  	'fire-storm', 'fire-chaser',
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
        array('level' => 0, 'token' => 'fire-storm'),
        array('level' => 10, 'token' => 'fire-chaser')
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