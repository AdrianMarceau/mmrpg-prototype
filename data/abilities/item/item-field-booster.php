<?
// ITEM : FIELD BOOSTER
$ability = array(
  'ability_name' => 'Field Booster',
  'ability_token' => 'item-field-booster',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/StatBoosters',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'copy',
  'ability_description' => 'A mysterious disc containing some kind of elemental booster program.  When held by a robot master, this item increases the field multiplier matching the user\'s core type by up to {RECOVERY2}% at end of each turn in battle.',
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