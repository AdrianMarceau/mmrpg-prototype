<?
// TOAD MAN
$robot = array(
  'robot_number' => 'DCN-026',
  'robot_game' => 'MM04',
  'robot_name' => 'Toad Man',
  'robot_token' => 'toad-man',
  'robot_image_editor' => 18,
  'robot_image_size' => 80,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Toad Man (Watermelon Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Toad Man (Blueberry Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Toad Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'water',
  'robot_field' => 'rainy-sewers',
  'robot_description' => 'Hopping Farmer Robot',
  'robot_description2' => 'A series first created for irrigation of crops during times of drought, they found that they could create rain of acid. The Rain Flush is an ability in which they shoot a pod containing a vapor of acid, which then forms into acid rain coming down onto their opponents, melting their exterior skeleton. Besides that, they show great jumping skills and jump long distances. They usually enjoys natural science and enjoys triple-jumping and long-jumping. However, they\'re tone-deaf and often find themselves uncomfortable with the company of a Snake Man. In the end, they have devastating ability, great jumping prowess, and have an uncomfortable look to match.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('earth', 'nature'), //drill-bomb,search-snake
  'robot_affinities' => array('water'),
  'robot_abilities' => array(
  	'rain-flush',
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
        array('level' => 0, 'token' => 'rain-flush')
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