<?
// CUT MAN
$robot = array(
  'robot_number' => 'DLN-003',
  'robot_game' => 'MM01',
  'robot_name' => 'Cut Man',
  'robot_token' => 'cut-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Cut Man (Blue Alt)', 'summons' => 100, 'colour' => 'water'),
    array('token' => 'alt2', 'name' => 'Cut Man (Yellow Alt)', 'summons' => 200, 'colour' => 'electric'),
    array('token' => 'alt9', 'name' => 'Cut Man (Darkness Alt)', 'summons' => 900, 'colour' => 'empty')
    ),
  'robot_core' => 'cutter',
  'robot_description' => 'Giant Scissor Robot',
  'robot_field' => 'abandoned-warehouse',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'flame'),
  'robot_resistances' => array('missile'),
  'robot_abilities' => array(
  	'rolling-cutter', 'rising-cutter',
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
        array('level' => 0, 'token' => 'rolling-cutter'),
        array('level' => 6, 'token' => 'rising-cutter')
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