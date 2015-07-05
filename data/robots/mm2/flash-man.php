<?
// FLASH MAN
$robot = array(
  'robot_number' => 'DWN-014',
  'robot_game' => 'MM02',
  'robot_name' => 'Flash Man',
  'robot_token' => 'flash-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Flash Man (Red Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Flash Man (Green Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Flash Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'time',
  'robot_description' => 'Temporal Shift Robot',
  'robot_field' => 'photon-collider',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'freeze'),
  'robot_resistances' => array('shadow'),
  'robot_immunities' => array('time'),
  'robot_abilities' => array(
  	'flash-stopper',
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
        array('level' => 0, 'token' => 'flash-stopper')
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