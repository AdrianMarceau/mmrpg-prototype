<?
// DIVE TORPEDO
$ability = array(
  'ability_name' => 'Dive Torpedo',
  'ability_token' => 'dive-torpedo',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/031',
  'ability_master' => 'dive-man',
  'ability_number' => 'DCN-031',
  'ability_description' => 'The user propells itself toward the target using a high speed jet of water to deal massive damage with a {RECOVERY2}% chance of a critical hit!',
  'ability_type' => 'missile',
  'ability_type2' => 'water',
  'ability_energy' => 8,
  'ability_damage' => 24,
  'ability_recovery2' => 20,
  'ability_accuracy' => 98,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>