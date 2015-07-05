<?
// NEEDLE MAN
$robot = array(
  'robot_number' => 'DWN-017',
  'robot_game' => 'MM03',
  'robot_name' => 'Needle Man',
  'robot_token' => 'needle-man',
  'robot_image_editor' => 110,
  'robot_image_size' => 80,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Needle Man (Magenta Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Needle Man (Emerald Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Needle Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'cutter',
  'robot_field' => 'construction-site',
  'robot_description' => 'Deadly Spikes Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('crystal', 'wind'), //gemini-laser,air-shooter
  'robot_resistances' => array('nature'),
  'robot_abilities' => array(
  	'needle-cannon',
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
        array('level' => 0, 'token' => 'needle-cannon')
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