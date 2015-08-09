<?
// BASS
$robot = array(
  'robot_number' => 'SWN-001',
  'robot_game' => 'MM00',
  'robot_name' => 'Bass',
  'robot_token' => 'bass',
  'robot_image_editor' => 4117,
  'robot_core' => 'copy',
  'robot_description' => 'Strongest Challenger Robot',
  'robot_description2' => 'The original Bass was created to scrap the original Mega Man. They are now used as a weapon of war. The Bass series are usually narcissists and are not often loyal. They are equipped with the Bass Buster and variable weapon system(copy chip). Some Bass units have an adaptor that allows them to fuse with their support unit. The Bass series may be cocky but have great power and ablities to back up that confidence.',
  'robot_field' => 'wily-castle',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('flame', 'shadow'), // bass is very suseptible to evil energy so...
  'robot_resistances' => array('space'),
  'robot_abilities' => array(
    'buster-shot', 'buster-charge', 'buster-relay',
    'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
    'attack-break', 'defense-break', 'speed-break', 'energy-break',
    'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
    'attack-support', 'defense-support', 'speed-support', 'energy-support',
    'attack-assault', 'defense-assault', 'speed-assault', 'energy-assault',
    'attack-shuffle', 'defense-shuffle', 'speed-shuffle', 'energy-shuffle',
    'attack-mode', 'defense-mode', 'speed-mode', 'repair-mode',
    'experience-booster', 'recovery-booster', 'damage-booster',
    'experience-breaker', 'recovery-breaker', 'damage-breaker',
    'field-support', 'mecha-support',
    'mega-buster', 'proto-buster', 'roll-buster', 'disco-buster', 'rhythm-buster',
    'light-buster', 'wily-buster', 'cossack-buster',
    'mega-ball', 'mega-slide', 'proto-shield', 'proto-strike'
    ),
  'robot_rewards' => array(
    'abilities' => array(
      array('level' => 0, 'token' => 'buster-shot'), // = 10
      array('level' => 12, 'token' => 'bass-buster'), // +2
      array('level' => 16, 'token' => 'bass-crush'), // +4
      array('level' => 22, 'token' => 'bass-baroque') // +6
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