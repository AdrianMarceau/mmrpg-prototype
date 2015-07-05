<?
// METAL MAN
$robot = array(
  'robot_number' => 'DWN-009',
  'robot_game' => 'MM02',
  'robot_name' => 'Metal Man',
  'robot_token' => 'metal-man',
  'robot_image_editor' => 18,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Metal Man (Blue Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Metal Man (Green Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Metal Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'cutter',
  'robot_description' => 'Lethal Blades Robot',
  'robot_description2' => 'This unit is an upgrade on the Cut Man series and fight with deadly skill. They have the Metal Blade, one of the most deadly attacks ever created and has been said to have took down an entire army. They have great speed and like to fight on conveyor belts. They have also been called dentists of the future, jokingly. This unit is often sarcastic and not to be trusted by anyone. They like playing with flying discs but hate when dogs catch them instead. This unit is very powerful but some rumors suggest it can be defeated with their own weapon...',
  'robot_field' => 'industrial-facility',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('swift', 'cutter'),
  'robot_resistances' => array('wind', 'nature'),
  'robot_abilities' => array(
  	'metal-blade',
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
        array('level' => 0, 'token' => 'metal-blade')
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