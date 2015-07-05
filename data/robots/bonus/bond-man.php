<?
// BOND MAN
$robot = array(
  'robot_number' => 'RPG-00B', // Prototype RPG
  'robot_game' => 'MMRPG',
  'robot_name' => 'Bond Man',
  'robot_token' => 'bond-man',
  'robot_description' => 'Industrial Bonding Robot',
  'robot_image_editor' => 9999, // non-member
  'robot_core' => '',
  'robot_field' => 'intro-field',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter'),
  'robot_resistances' => array('flame', 'freeze', 'water', 'electric'),
  'robot_abilities' => array(
  	'sticky-shot', 'sticky-bond',
  	'buster-shot', 'buster-charge',
    'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
    'attack-break', 'defense-break', 'speed-break', 'energy-break',
    'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
    'attack-support', 'defense-support', 'speed-support', 'energy-support',
    'attack-assault', 'defense-assault', 'speed-assault', 'energy-assault',
    'attack-shuffle', 'defense-shuffle', 'speed-shuffle', 'energy-shuffle',
    'attack-mode', 'defense-mode', 'speed-mode', 'repair-mode',
    'experience-booster', 'recovery-booster', 'damage-booster',
    'experience-breaker', 'recovery-breaker', 'damage-breaker',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'sticky-shot'),
        array('level' => 2, 'token' => 'sticky-bond')
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