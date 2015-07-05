<?
// TOP MAN
$robot = array(
  'robot_number' => 'DWN-021',
  'robot_game' => 'MM03',
  'robot_name' => 'Top Man',
  'robot_token' => 'top-man',
  'robot_image_editor' => 110,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Top Man (Purple Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Top Man (Green Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Top Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'swift',
  'robot_field' => 'spinning-greenhouse',
  'robot_description' => 'Dizzy Spinning Robot',
  'robot_description2' => 'First created to find energy elements on foreign planets, this unit is incredibly agile. The Top Man unit have the auto-balance system, which allows them to spin rapidly, called the Top Spin. They\'re good at wheel-greasing and making necessary arrangements but are bad story-tellers and are luck pushers. They like to ice-skate but hate bad dancers. The Top Man unit, in short are very agile and have the fighting style of a dancer.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'explode'), //hard-knuckle
  'robot_resistances' => array('time', 'laser'),
  'robot_abilities' => array(
  	'top-spin',
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
        array('level' => 0, 'token' => 'top-spin')
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