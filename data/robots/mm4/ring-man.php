<?
// RING MAN
$robot = array(
  'robot_number' => 'DCN-029',
  'robot_game' => 'MM04',
  'robot_name' => 'Ring Man',
  'robot_token' => 'ring-man',
  'robot_image_editor' => 18,
  'robot_core' => 'space',
  'robot_field' => 'space-simulator',
  'robot_description' => 'Ring Juggling Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'swift'),
  'robot_resistances' => array('wind'),
  'robot_abilities' => array(
  	'ring-boomerang',
  	'buster-shot',
  	'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
    'energy-boost', 'energy-break', 'energy-swap', 'repair-mode',
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