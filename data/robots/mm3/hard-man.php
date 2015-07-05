<?
// HARD MAN
$robot = array(
  'robot_number' => 'DWN-020',
  'robot_game' => 'MM03',
  'robot_name' => 'Hard Man',
  'robot_token' => 'hard-man',
  'robot_image_editor' => 110,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Hard Man (Golden Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Hard Man (Diamond Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Hard Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_image_size' => 80,
  'robot_core' => 'impact',
  'robot_field' => 'rocky-plateau',
  'robot_description' => 'Titanium Heavyweight Robot',
  'robot_description2' => 'Being made of ceratanium, this unit is very defensive and offensive. This unit is very heavy and can cause earthquakes simply by jumping. They use the Hard Knuckle, a attack in which they launch their arm to smash the opponent. One problem by his heaviness is that his speed takes a remarkable toll. This unit is known as a man of few words which like to play fair with their opponents, unlike the Snake Man unit. Their hobbies include Sumo Wrestling but dislike swimming. This unit is NOT to be taken lightly and is a threat when in battle.',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('missile', 'impact'), //magnet-missile
  'robot_resistances' => array('cutter', 'freeze', 'flame'),
  'robot_abilities' => array(
  	'hard-knuckle',
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
        array('level' => 0, 'token' => 'hard-knuckle')
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