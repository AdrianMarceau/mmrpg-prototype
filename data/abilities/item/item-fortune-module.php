<?
// ITEM : FORTUNE MODULE
$ability = array(
  'ability_name' => 'Fortune Module',
  'ability_token' => 'item-fortune-module',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Modules',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => '',
  'ability_type2' => 'electric',
  'ability_description' => 'A mysterious chip that improves holder\'s luck in battle.  When held by a robot master, this item greatly increases the chance of a critical hit and causes enemies to drop items more frequently.',
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