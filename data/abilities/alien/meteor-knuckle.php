<?
// METEOR KNUCKLE
$ability = array(
  'ability_name' => 'Meteor Knuckle',
  'ability_token' => 'meteor-knuckle',
  'ability_game' => 'MMEXE',
  'ability_description' => '...',
  'ability_type' => 'space',
  'ability_type2' => 'impact',
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