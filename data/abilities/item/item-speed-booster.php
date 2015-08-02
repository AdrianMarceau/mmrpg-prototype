<?
// ITEM : SPEED BOOSTER
$ability = array(
  'ability_name' => 'Speed Booster',
  'ability_token' => 'item-speed-booster',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/StatBoosters',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'speed',
  'ability_description' => 'A mysterious disc containing some kind of speed booster program.  When held by a robot master, this item increases the user\'s speed stat by {RECOVERY2}% at the end of each turn in battle.',
  'ability_energy' => 0,
  'ability_recovery2' => 20,
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