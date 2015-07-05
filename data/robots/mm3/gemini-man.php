<?
// GEMINI MAN
$robot = array(
  'robot_number' => 'DWN-019',
  'robot_game' => 'MM03',
  'robot_name' => 'Gemini Man',
  'robot_token' => 'gemini-man',
  'robot_image_editor' => 110,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Gemini Man (Kunzite Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Gemini Man (Emerald Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Gemini Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_image_size' => 80,
  'robot_core' => 'crystal',
  'robot_field' => 'reflection-chamber',
  'robot_description' => 'Twin Fighters Robot',
  'robot_description2' => 'The Gemini Man unit was first created by Dr. Thomas Light and Dr. Albert Wily. They have the weapon called Gemini Laser, a beam that bounces off walls to lay multiple hits. They can also clone themselves up to four times, making them VERY strategic fighters and can confuse many of their opponents. They have been seen to sometimes just look at their copies like a mirror. They shown traits of narcissism, but have a deep hatred of snakes. They are not to be messed with, as they have tricky fighting styles and tricks.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('nature', 'flame'), //search-snake
  'robot_resistances' => array('freeze', 'electric'),
  'robot_abilities' => array(
  	'gemini-laser',
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
        array('level' => 0, 'token' => 'gemini-laser')
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