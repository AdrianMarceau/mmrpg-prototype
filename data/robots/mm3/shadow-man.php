<?
// SHADOW MAN
$robot = array(
  'robot_number' => 'DWN-024',
  'robot_game' => 'MM03',
  'robot_name' => 'Shadow Man',
  'robot_token' => 'shadow-man',
  'robot_image_editor' => 110,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Shadow Man (Orange Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Shadow Man (Purple Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Shadow Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'shadow',
  'robot_field' => 'septic-system',
  'robot_description' => 'Ninja Assasin Robot',
  'robot_description2' => 'The original Shadow Man was first found by Dr. Wily in an abandoned temple. Reproductions of this unit are hard to make due to it\'s alien-like material. This unit is very sneaky, agile, and has a blade covered in a deadly substance called the Shadow Blade, as well as other ninja techniques and weapons. They are very impulsive and dislikes obvious tricks, but likes to surprise others. their origin is unknown, but something that is known about him is that they are very deadly enemies.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'laser'), //top-spin
  'robot_immunities' => array('shadow'),
  'robot_abilities' => array(
  	'shadow-blade',
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
        array('level' => 0, 'token' => 'shadow-blade')
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