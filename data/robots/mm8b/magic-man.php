<?
// MAGIC MAN
$robot = array(
  'robot_number' => 'KGN-006',
  'robot_game' => 'MM085',
  'robot_name' => 'Magic Man',
  'robot_token' => 'magic-man',
  'robot_core' => 'shadow',
  'robot_description' => 'Showy Illusionist Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'flame'),
  'robot_resistances' => array('shadow', 'freeze'),
  'robot_abilities' => array(
  	'magic-card',
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
        array('level' => 0, 'token' => 'magic-card')
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