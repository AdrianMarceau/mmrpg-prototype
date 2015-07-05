<?
// CHARGE MAN
$robot = array(
  'robot_number' => 'DWN-038',
  'robot_game' => 'MM05',
  'robot_name' => 'Charge Man',
  'robot_token' => 'charge-man',
  'robot_image_editor' => 18,
  'robot_image_size' => 80,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Charge Man (Steel Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Charge Man (Copper Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Charge Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'swift',
  'robot_description' => 'Chugging Locomotive Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('earth', 'water'), //power-stone,rain-flush
  'robot_resistances' => array('flame', 'wind'),
  'robot_abilities' => array(
  	'charge-kick',
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
        array('level' => 0, 'token' => 'charge-kick')
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