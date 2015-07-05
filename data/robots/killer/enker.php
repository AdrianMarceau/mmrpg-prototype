<?
// ENKER
$robot = array(
  'robot_number' => 'MKN-001',
  'robot_game' => 'MM20',
  'robot_name' => 'Enker',
  'robot_token' => 'enker',
  'robot_class' => 'boss',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_core' => 'shield',
  'robot_description' => 'Prototype Megaman Hunter',
  'robot_description2' => 'Enker was created to scrap Mega Man, being the first in the Mega Man Killer series. Now this series does not just hunt Mega Man but any robot it sees as a threat. They are made to kill, and have great fighting skill. Their Mirror Buster is a buster in which any shot sent to them will be reflected back to the opponent. This series does show any compassion except on New Year\'s eve and believe in staying true to themselves. They also do not like foreign robots and enjoy Nabeyaki-Udon. They have a strong Japanese spirit and destroy any robot no matter what the cost.',
  'robot_energy' => 100,
  'robot_weapons' => 14,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'explode'),
  'robot_resistances' => array('shadow'),
  'robot_abilities' => array(
  	'mirror-buster',
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
        array('level' => 0, 'token' => 'mirror-buster')
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