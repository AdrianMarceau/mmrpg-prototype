<?
// RAIN DANCE
$ability = array(
  'ability_name' => 'Rain Dance',
  'ability_token' => 'rain-dance',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/026',
  'ability_master' => 'toad-man',
  'ability_number' => 'DCN-026',
  'ability_description' => 'The user engages in a mysterious dance that summons heavy rain to the battlefield, restoring up to {RECOVERY}% life and weapon energy for all Water, Freeze, and Nature core robots!',
  'ability_type' => 'water',
  'ability_energy' => 8,
  'ability_recovery' => 20,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>