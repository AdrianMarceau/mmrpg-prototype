<?
// QUICK MAN
$robot = array(
  'robot_number' => 'DWN-012',
  'robot_game' => 'MM02',
  'robot_name' => 'Quick Man',
  'robot_token' => 'quick-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Quick Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Quick Man (Green Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Quick Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'swift',
  'robot_description' => 'Lightspeed Boomerang Robot',
  'robot_description2' => 'Quick Man was designed to be the fastest robot, and his unit is still considered to be the fastest made. This unit excels in speed and utilizes the Quick Boomerang, a fast-moving blade that returns to it\'s user when the user wills it. This unit is seen as a leader but is impulsive and impatient, always wanting to move. This unit has a friendly rivalry with the Turbo Man unit and anything it sees as fast. They seem to be just going with the flow of battle, but are actually very strategic. There is a rumor saying that their speed is achieved by a device in which they speed up time in their area, but the only one who knows if this is true is the Quick Man unit itself.',
  'robot_field' => 'underground-laboratory',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('time', 'explode'),
  'robot_resistances' => array('swift'),
  'robot_immunities' => array('impact'),
  'robot_abilities' => array(
  	'quick-boomerang',
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
        array('level' => 0, 'token' => 'quick-boomerang')
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