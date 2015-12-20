<?
// RHYTHM HEAVEN
$ability = array(
  'ability_name' => 'Rhythm Heaven',
  'ability_token' => 'rhythm-heaven',
  'ability_game' => 'MM03',
  'ability_group' => 'MM00/Weapons/Rhythm',
  'ability_description' => 'The user summons a satellite behind the target that amplifies its power and doubles the damage dealt by attacks for three turns!',
  'ability_type' => '',
  'ability_energy' => 8,
  'ability_accuracy' => 100,
  'ability_damage2' => 50,
  'ability_damage2_percent' => true,
  'ability_target' => 'select_this',
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>