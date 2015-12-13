<?
// Energy Fist
$ability = array(
  'ability_name' => 'Energy Fist',
  'ability_token' => 'energy-fist',
  'ability_class' => 'boss',
  'ability_game' => 'MMEXE',
  'ability_description' => '...',
  'ability_type' => '',
  'ability_damage' => 10,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

  }
  );
?>