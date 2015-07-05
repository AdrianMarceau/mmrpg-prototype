<?
// RING MAN
$robot = array(
  'robot_number' => 'DCN-029',
  'robot_game' => 'MM04',
  'robot_name' => 'Ring Man',
  'robot_token' => 'ring-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Ring Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Ring Man (Green Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Ring Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'cutter',
  'robot_field' => 'space-simulator',
  'robot_description' => 'Ring Juggling Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'swift'), //pharaoh-soul,solar-blaze,(leaf-shield)
  'robot_resistances' => array('wind'),
  'robot_abilities' => array(
  	'ring-boomerang',
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
        array('level' => 0, 'token' => 'ring-boomerang'),
        //array('level' => 6, 'token' => 'ring-throw')
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