<?
// SKULL MAN
$robot = array(
  'robot_number' => 'DCN-032',
  'robot_game' => 'MM04',
  'robot_name' => 'Skull Man',
  'robot_token' => 'skull-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Skull Man (Golden Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Skull Man (Crystal Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Skull Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'shadow',
  'robot_field' => 'robosaur-boneyard',
  'robot_description' => 'Dead Executor Robot',
  'robot_description2' => 'A series created specifically to fight with great skill. His technique, the Skull Barrier, is very defensive and block almost all attacks. Besides that, his skill in fighting is almost unmatched and can take down opponents much larger than him. However, being made only to fight made to fight makes him socially awkward and alienates others by acting in bad taste. He likes horror movies, which goes with his morbid appearance. He\'s a great fighter who moves with the eye of the tiger.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('wind', 'explode'), //dust-crusher, oil-slider
  'robot_resistances' => array('space', 'time', 'shadow'),
  'robot_abilities' => array(
  	'skull-barrier',
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
        array('level' => 0, 'token' => 'skull-barrier')
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