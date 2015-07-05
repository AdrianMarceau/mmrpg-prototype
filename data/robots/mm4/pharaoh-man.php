<?
// PHARAOH MAN
$robot = array(
  'robot_number' => 'DCN-028',
  'robot_game' => 'MM04',
  'robot_name' => 'Pharaoh Man',
  'robot_token' => 'pharaoh-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Pharaoh Man (Cursed Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Pharaoh Man (Blessed Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Pharaoh Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'flame',
  'robot_field' => 'egyptian-excavation',
  'robot_description' => 'Ancient Investigator Robot',
  'robot_description2' => 'Designed to explore dark areas, the Pharaoh Man unit has great control over fire and such. They have great fighting skill and have the Pharaoh Shot, a condensed ball of fire. They like treasure, dislike raiders and is said to be a charismatic leader. They have over 1,000 Mummira but none of that matters when they get near beautiful women. All in all, this unit are great leaders and very powerful fighters.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('time', 'space'), //pharaoh-wave, gemini-laser
  'robot_resistances' => array('shadow', 'flame'),
  'robot_abilities' => array(
  	'pharaoh-soul', //'pharaoh-wave', 'pharaoh-curse',
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
        array('level' => 0, 'token' => 'pharaoh-soul'),
        //array('level' => 6, 'token' => 'speed-blaze'),
        //array('level' => 10, 'token' => 'pharaoh-wave'),
        //array('level' => 10, 'token' => 'speed-burn'),
        //array('level' => 20, 'token' => 'pharaoh-curse'),
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