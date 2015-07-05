<?
// CRASH MAN
$robot = array(
  'robot_number' => 'DWN-013',
  'robot_game' => 'MM02',
  'robot_name' => 'Crash Man',
  'robot_token' => 'crash-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Crash Man (Green Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Crash Man (Blue Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Crash Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'explode',
  'robot_description' => 'Aggressive Bomber Robot',
  'robot_field' => 'pipe-station',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('wind', 'shield'),
  'robot_resistances' => array('explode', 'cutter'),
  'robot_abilities' => array(
  	'crash-bomber',
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
        array('level' => 0, 'token' => 'crash-bomber'),
        //array('level' => 6, 'token' => 'crash-driller'),
        //array('level' => 10, 'token' => 'crash-avenger')
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