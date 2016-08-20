<?
// ICE MAN
$robot = array(
  'robot_number' => 'DLN-005',
  'robot_game' => 'MM01',
  'robot_name' => 'Ice Man',
  'robot_token' => 'ice-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Ice Man (Pink Alt)', 'summons' => 100, 'colour' => 'laser'),
    array('token' => 'alt2', 'name' => 'Ice Man (Mint Alt)', 'summons' => 200, 'colour' => 'energy'),
    array('token' => 'alt9', 'name' => 'Ice Man (Darkness Alt)', 'summons' => 900, 'colour' => 'empty')
    ),
  'robot_core' => 'freeze',
  'robot_description' => 'Arctic Exploration Robot',
  'robot_field' => 'arctic-jungle',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'explode'),
  'robot_resistances' => array('flame'),
  'robot_immunities' => array('freeze'),
  'robot_abilities' => array(
  	'ice-breath', 'ice-slasher',
  	'buster-shot',
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
        array('level' => 0, 'token' => 'ice-breath'),
        array('level' => 6, 'token' => 'ice-slasher')
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