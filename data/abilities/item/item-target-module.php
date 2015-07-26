<?
// ITEM : TARGET MODULE
$ability = array(
  'ability_name' => 'Target Module',
  'ability_token' => 'item-target-module',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Modules',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => '',
  'ability_type2' => 'attack',
  'ability_description' => 'A mysterious chip that improves the targeting capabilities of the holder.  When held by a robot master, this item allows abilities that typically only reach the front row to target benched robots as well.',
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