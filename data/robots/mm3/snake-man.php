<?
// SNAKE MAN
$robot = array(
  'robot_number' => 'DWN-022',
  'robot_game' => 'MM03',
  'robot_name' => 'Snake Man',
  'robot_token' => 'snake-man',
  'robot_image_editor' => 110,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Snake Man (Amber Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Snake Man (Crimson Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Snake Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'nature',
  'robot_field' => 'serpent-column',
  'robot_description' => 'Stealthy Serpent Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'electric'), //needle-cannon
  'robot_resistances' => array('nature', 'shadow'),
  'robot_abilities' => array(
  	'search-snake',
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
        array('level' => 0, 'token' => 'search-snake')
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