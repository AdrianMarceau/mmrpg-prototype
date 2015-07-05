<?
// CUT MAN
$robot = array(
  'robot_number' => 'DLN-003',
  'robot_game' => 'MM01',
  'robot_name' => 'Cut Man',
  'robot_token' => 'cut-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Cut Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Cut Man (Yellow Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Cut Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'cutter',
  'robot_description' => 'Giant Scissor Robot',
  'robot_description2' => 'The Cut Man unit was designed to be a lumber-chopping robot, and does so by the cutter on their head. They have the technique, Rolling Cutter, a move in which they throw their Cutter on the top of their head and cuts down most opponents to size. Some units have the Rising Cutter which is a technique in which they release a giant cutter below the opponent. Most have shown personality traits of children and making awful cutting puns. The Cut Man unit is one of the most used units in the world for their versatility and their simplicity.',
  'robot_field' => 'abandoned-warehouse',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'flame'),
  'robot_resistances' => array('missile'),
  'robot_abilities' => array(
  	'rolling-cutter', 'rising-cutter',
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
        array('level' => 0, 'token' => 'rolling-cutter'),
        array('level' => 10, 'token' => 'rising-cutter')
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