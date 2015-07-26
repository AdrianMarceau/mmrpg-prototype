<?
// ITEM : GROWTH MODULE
$ability = array(
  'ability_name' => 'Growth Module',
  'ability_token' => 'item-growth-module',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Modules',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => '',
  'ability_type2' => 'energy',
  'ability_description' => 'A mysterious chip that improves the holder\'s experiences in battle.  When held by a robot master, this item doubles experience points and bonus stats earned upon defeating a target.',
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