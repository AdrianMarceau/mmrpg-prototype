<?
// BRIGHT MAN
$robot = array(
  'robot_number' => 'DCN-025',
  'robot_game' => 'MM04',
  'robot_name' => 'Bright Man',
  'robot_token' => 'bright-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Bright Man (Green Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Bright Man (Purple Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Bright Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'time',
  'robot_field' => 'lighting-control',
  'robot_description' => 'Bright Thinking Robot',
  'robot_description2' => 'First created to light areas too dark for human eyes, the Bright Man series found that their flash has the ability to produce light strong enough to paralyze some robots. The technique it owns, Bright Burst can emit a ten million watts, which allows it to attack enemies with ease. They are usually intelligent and likes to invent objects in it\'s spare time. However it is a chatter box and might bore their opponents to give up during battles, making it\'s mouth arguably it\'s best weapon. Their Bright Burst is a dangerous weapon and they have a mouth to match with it.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'space'), //rain-flush, star-crash
  'robot_resistances' => array('impact', 'time'),
  'robot_abilities' => array(
  	'bright-burst',
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
        array('level' => 0, 'token' => 'bright-burst')
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