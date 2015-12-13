<?
// TRILL AURA
$ability = array(
  'ability_name' => 'Trill Aura',
  'ability_token' => 'trill-aura',
  'ability_game' => 'MMEXE',
  'ability_class' => 'boss',
  'ability_description' => 'The user surrounds itself in an aura that grants a temporarily immunity to all elemental damage for three turns!',
  'ability_type' => 'space',
  'ability_damage' => 0,
  'ability_accuracy' => 100,
  'ability_energy' => 8,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

  }
  );
?>