<?
// ELEC MAN
$robot = array(
  'robot_number' => 'DLN-008',
  'robot_game' => 'MM01',
  'robot_name' => 'Elec Man',
  'robot_token' => 'elec-man',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Elec Man (Green Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Elec Man (Blue Alt)', 'summons' => 200),
    array('token' => 'alt9', 'name' => 'Elec Man (Darkness Alt)', 'summons' => 900)
    ),
  'robot_core' => 'electric',
  'robot_description' => 'High Voltage Robot',
  'robot_description2' => 'When first created, the Elec Man series was considered to be the most powerful robots ever created. With the advancements of science, more robots were being made with some attributes similar to Elec Man. The Elec Man series can still keep up with newer models thanks to their powerful control over electricity and their Thunder Beam, a highly powerful beam of electricity. This series was created to watch over production in nuclear power plants but are also great fighters. This series usually have personality traits of leadership and quick judgement, but also brag a lot, talking about their "shocking beauty". In short, the Elec Man series are very capable fighters and have great control over electricity.',
  'robot_field' => 'electrical-tower',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weaknesses' => array('cutter', 'earth'),
  'robot_resistances' => array('freeze', 'time'),
  'robot_affinities' => array('electric'),
  'robot_abilities' => array(
  	'thunder-strike', 'thunder-beam',
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
        array('level' => 0, 'token' => 'thunder-strike'),
        array('level' => 10, 'token' => 'thunder-beam')
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