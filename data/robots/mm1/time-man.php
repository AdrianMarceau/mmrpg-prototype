<?
// TIME MAN
$robot = array(
  'robot_number' => 'DLN-00A',
  'robot_game' => 'MM01',
  'robot_name' => 'Time Man',
  'robot_token' => 'time-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Time Man (Green Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Time Man (Orange Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Time Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'time',
  'robot_description' => 'Prototype Time-Control Robot',
  'robot_field' => 'clock-citadel',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'nature'),
  'robot_immunities' => array('time'),
  'robot_abilities' => array(
  	'time-arrow', 'time-slow',
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
        array('level' => 0, 'token' => 'time-arrow'),
        array('level' => 10, 'token' => 'time-slow')
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