<?
// DUST MAN
$robot = array(
  'robot_number' => 'DCN-030',
  'robot_game' => 'MM04',
  'robot_name' => 'Dust Man',
  'robot_token' => 'dust-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Dust Man (Purple Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Dust Man (Green Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Dust Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'wind',
  'robot_field' => 'rusty-scrapheap',
  'robot_description' => 'Scrap Contracting Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'explode'), //ring-boomerang,(old)drill-bomb
  'robot_affinities' => array('wind'),
  'robot_abilities' => array(
  	'dust-crusher',
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
        array('level' => 0, 'token' => 'dust-crusher')
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