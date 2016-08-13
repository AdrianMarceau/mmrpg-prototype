<?
// SKELETON JOE
$robot = array(
  'robot_number' => 'SJOE-002', // ROBOT : SKELETON JOE (1st Gen)
  'robot_game' => 'MMRPG',
  'robot_name' => 'Skeleton Joe',
  'robot_token' => 'skeleton-joe',
  'robot_description' => 'Bone Boomerang Mecha',
  'robot_image_editor' => 412,
  'robot_class' => 'mecha',
  'robot_core' => 'shadow',
  'robot_field' => 'intro-field',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('explode', 'crystal'),
  'robot_resistances' => array('water', 'flame', 'electric', 'nature'),
  'robot_abilities' => array(
  	'buster-shot', 'buster-charge',
    'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
    'attack-break', 'defense-break', 'speed-break', 'energy-break',
    'attack-mode', 'defense-mode', 'speed-mode', 'repair-mode',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
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