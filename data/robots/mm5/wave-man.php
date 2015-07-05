<?
// WAVE MAN
$robot = array(
  'robot_number' => 'DWN-034',
  'robot_game' => 'MM05',
  'robot_name' => 'Wave Man',
  'robot_token' => 'wave-man',
  'robot_image_editor' => 18,
  'robot_image_size' => 80,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Wave Man (Seaweed Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Wave Man (Royalty Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Wave Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'water',
  'robot_description' => 'Amphibious Assault Robot',
  'robot_description2' => 'The Wave Man series is a series of robots meant for amphious combat and as such are skilled in aquatic regions. The Wave Man series is great with long-range combat, being equipped with a harpoon and can make powerful water waves, but do not have good close-range combat weapons. This series is very anti-social and attacks anyone who comes near. Seeing sludge provokes them even more, which makes them very dangerous opponents.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'missile'), //charge-kick,dive-missile
  'robot_resistances' => array('flame'),
  'robot_immunities' => array('water'),
  'robot_abilities' => array(
  	'water-wave',
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
        array('level' => 0, 'token' => 'water-wave')
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