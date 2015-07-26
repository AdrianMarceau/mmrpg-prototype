<?
// ITEM : ENERGY BOOSTER
$ability = array(
  'ability_name' => 'Energy Booster',
  'ability_token' => 'item-energy-booster',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/StatBoosters',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'energy',
  'ability_description' => 'A mysterious disc containing some kind of energy booster program.  When held by a robot master, this item recovers the user\'s life energy by up to {RECOVERY2}% at end of each turn in battle.',
  'ability_energy' => 0,
  'ability_recovery2' => 10,
  'ability_recovery2_percent' => true,
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