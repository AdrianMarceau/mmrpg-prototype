<?
// SLUR AURA
$ability = array(
  'ability_name' => 'Slur Aura',
  'ability_token' => 'slur-aura',
  'ability_game' => 'MMEXE',
  'ability_class' => 'boss',
  'ability_description' => 'The user surrounds itself in an aura that grants a temporarily affinity to all elemental damage for three turns!',
  'ability_type' => 'space',
  'ability_damage' => 0,
  'ability_accuracy' => 100,
  'ability_energy' => 16,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

  }
  );
?>