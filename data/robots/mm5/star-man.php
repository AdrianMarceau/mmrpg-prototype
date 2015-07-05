<?
// STAR MAN
$robot = array(
  'robot_number' => 'DWN-037',
  'robot_game' => 'MM05',
  'robot_name' => 'Star Man',
  'robot_token' => 'star-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Star Man (Solar Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Star Man (Lunar Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Star Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'space',
  'robot_description' => 'Interstellar Research Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('water', 'earth'), //water-wave,power-stone
  'robot_affinities' => array('space'),
  'robot_abilities' => array(
  	'star-crash',
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
        array('level' => 0, 'token' => 'star-crash')
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