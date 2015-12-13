<?
// SLUR TWISTER
$ability = array(
  'ability_name' => 'Slur Twister',
  'ability_token' => 'slur-twister',
  'ability_game' => 'MMEXE',
  'ability_class' => 'boss',
  'ability_description' => 'The user slides at the target from across the field, creating a large cosmic vortex that deals massive damage!',
  'ability_type' => 'space',
  'ability_damage' => 66,
  'ability_accuracy' => 96,
  'ability_energy' => 32,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

  }
  );
?>