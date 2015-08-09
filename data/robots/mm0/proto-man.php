<?
// PROTO MAN
$robot = array(
  'robot_number' => 'DLN-000',
  'robot_game' => 'MM00',
  'robot_name' => 'Proto Man',
  'robot_token' => 'proto-man',
  'robot_image_editor' => 412,
  'robot_core' => 'copy',
  'robot_description' => 'Renegade Prototype Robot',
  'robot_description2' => 'Originally the prototype of all robots, he became a renegade and stopped villainy in the shadows. Reproductions of this unit have been made over all the battling lately and have proved to be powerful fighters. They have the Proto Buster, Proto Shield, a copy chip, and the Proto Strike, a powerful technique capable of changing the tide of battle. They are usually resilient and like to make their own choices, but usually never show acts of evil. Despite being a prototype of robots, this unit has great power and speed with good whistling skill, too!',
  'robot_field' => 'cossack-citadel',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('impact', 'explode'),
  'robot_resistances' => array('laser'),
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
    'mega-buster', 'bass-buster', 'roll-buster', 'disco-buster', 'rhythm-buster',
    'light-buster', 'wily-buster', 'cossack-buster',
    'mega-ball', 'mega-slide', 'bass-crush', 'bass-baroque'
    ),
  'robot_rewards' => array(
    'abilities' => array(
       array('level' => 0, 'token' => 'buster-shot'), // = 20
       array('level' => 22, 'token' => 'proto-buster'), // +2
       array('level' => 26, 'token' => 'proto-shield'), // +4
       array('level' => 32, 'token' => 'proto-strike') // +6
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