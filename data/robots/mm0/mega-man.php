<?
// MEGA MAN
$robot = array(
  'robot_number' => 'DLN-001',
  'robot_game' => 'MM00',
  'robot_name' => 'Mega Man',
  'robot_token' => 'mega-man',
  'robot_image_editor' => 412,
  'robot_core' => 'copy',
  'robot_description' => 'Super Fighting Robot',
  'robot_field' => 'light-laboratory',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('electric', 'wind'),
  'robot_resistances' => array('shadow'),
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
    'bass-buster', 'proto-buster', 'roll-buster', 'disco-buster', 'rhythm-buster',
    'light-buster', 'wily-buster', 'cossack-buster',
    'bass-crush', 'bass-baroque', 'proto-shield', 'proto-strike'
    ),
  'robot_rewards' => array(
    'abilities' => array(
      array('level' => 0, 'token' => 'buster-shot'),
      array('level' => 2, 'token' => 'mega-buster'), // +2
      array('level' => 6, 'token' => 'mega-ball'), // +4
      array('level' => 12, 'token' => 'mega-slide') // +6
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