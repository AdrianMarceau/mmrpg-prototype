<?
// PROTO MAN COPY
$robot = array(
  'robot_number' => 'PCR-DWN',
  'robot_game' => 'MM00',
  'robot_name' => 'Proto Man S',
  'robot_token' => 'proto-man-copy',
  'robot_field' => 'final-destination-2',
  'robot_image_editor' => 412,
  'robot_core' => 'shadow',
  'robot_description' => '',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('earth', 'water', 'missile', 'space'), // first four robots by weakness
  'robot_resistances' => array('time', 'flame', 'wind', 'shield'), // last four robots by weakness
  'robot_affinities' => array('shadow'),
  'robot_abilities' => array(
    'buster-shot',
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
    'mega-buster', 'bass-buster', 'roll-buster', 'disco-buster', 'rhythm-buster',
    'light-buster', 'wily-buster', 'cossack-buster',
    'mega-ball', 'mega-slide', 'bass-crush', 'bass-baroque'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 2, 'token' => 'proto-buster'),
        array('level' => 4, 'token' => 'proto-shield'),
        array('level' => 8, 'token' => 'proto-strike'),
        array('level' => 16, 'token' => 'copy-shot')
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