<?
// ITEM : DEFENSE PROGRAM
$ability = array(
  'ability_name' => 'Defense Program',
  'ability_token' => 'item-defense-program',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Programs',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'defense',
  'ability_description' => 'A myserious disc containing the data for an ancient defense program.  When held by a robot master, this item doubles the user\'s base defense at the start of battle.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

  }
  );
?>