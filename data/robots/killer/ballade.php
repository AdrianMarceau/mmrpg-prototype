<?
// BALLADE
$robot = array(
  'robot_number' => 'MKN-003',
  'robot_game' => 'MM20',
  'robot_name' => 'Ballade',
  'robot_token' => 'ballade',
  'robot_class' => 'boss',
  'robot_image_editor' => 18,
  'robot_image_size' => 80,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Ballade (Powered Up)', 'summons' => 0)
    ),
  'robot_core' => 'explode',
  'robot_description' => 'Elite Megaman Hunter',
  'robot_description2' => 'Ballade was made to be the last in the Mega Man Killer unit and are very powerful, having great speed and power. They are equipped with the Ballade Cracker, a very explosive bomb capable of taking out multiple robots. They also have a second form, boosting their abilities even more. They only fight strong robots and believe themselves to be the strongest. They follow orders better than the Punk unit but are still very reckless. Although they believe themselves to be the strongest, they have great reason to see it that way.',
  'robot_energy' => 100,
  'robot_weapons' => 18,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'earth'),
  'robot_resistances' => array('shadow'),
  'robot_abilities' => array(
  	'ballade-cracker',
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
        array('level' => 0, 'token' => 'ballade-cracker')
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