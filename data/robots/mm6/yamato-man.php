<?
// YAMATO MAN
$robot = array(
  'robot_number' => 'MXN-048',
  'robot_game' => 'MM06',
  'robot_name' => 'Yamato Man',
  'robot_token' => 'yamato-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'cutter',
  'robot_description' => 'Honorable Samurai Robot',
  'robot_description2' => 'The Yamato Man unit were created to revitalize the samurai era of Japan, although they are mostly used for fighting. They were made to resemble a Japanese samurai and carry what looks like a heavy armor, but actually is very lightweight but doesn\'t cover defense. Their weapon is the Yamato Spear, an armor-piercing spear, but has to pick up the spear afterwards due to having a limited amount. they have the Japanese spirit and like the Knight Man unit due to having the same codes of honor. The Yamato Man unit is shy, loves collecting Japanese swords, and dislike speaking English. They are skilled warriors and fight with spirit, held high on their heads.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('earth', 'explode'), //silver-tomahawk,hyper-bomb
  'robot_resistances' => array('shadow', 'flame'),
  'robot_abilities' => array(
  	'yamato-spear',
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
        array('level' => 0, 'token' => 'yamato-spear')
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