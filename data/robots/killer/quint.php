<?
// QUINT
$robot = array(
  'robot_number' => 'SVN-001',
  'robot_game' => 'MM20',
  'robot_name' => 'Quint',
  'robot_token' => 'quint',
  'robot_class' => 'boss',
  'robot_image_editor' => 18,
  'robot_image_size' => 80,
  'robot_core' => 'swift',
  'robot_description' => 'Future Megaman Hunter',
  'robot_description2' => 'The original Quint was Mega Man from the future reprogrammed but reproductions of this unit are only copies of this robot. They possess the same skills as Mega Man, but have a weapon called Sakugarne which functions as some sort of cross between a jackhammer and a pogo-stick. This unit\'s personality is very sadistic but enjoys fortunetelling but hates time-zone fatigue. They do not like the Mega Man unit, but are they really hating themselves?',
  'robot_energy' => 100,
  'robot_weapons' => 12,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('time', 'space'),
  'robot_resistances' => array('shadow'),
  'robot_abilities' => array(
  	'sakugarne-hammer',
  	'buster-shot', 'buster-charge',
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
        array('level' => 0, 'token' => 'sakugarne-hammer')
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