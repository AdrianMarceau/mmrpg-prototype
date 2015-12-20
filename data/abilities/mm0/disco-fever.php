<?
// DISCO FEVER
$ability = array(
  'ability_name' => 'Disco Fever',
  'ability_token' => 'disco-fever',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Disco',
  'ability_description' => 'The user infects the target with a fever that torments their mind and causes their attacks to deal only half damage for the next three turns!',
  'ability_type' => '',
  'ability_energy' => 8,
  'ability_accuracy' => 100,
  'ability_damage2' => 50,
  'ability_damage2_percent' => true,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>