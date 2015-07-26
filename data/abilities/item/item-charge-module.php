<?
// ITEM : CHARGE MODULE
$ability = array(
  'ability_name' => 'Charge Module',
  'ability_token' => 'item-charge-module',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Modules',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => '',
  'ability_type2' => 'defense',
  'ability_description' => 'A mysterious chip that improves the charging capabilities of the holder.  When held by a robot master, this item allows abilities that typically require charging to instead be executed in a single turn at reduced power.',
  'ability_energy' => 0,
  'ability_damage2' => 2,
  'ability_damage2_percent' => true,
  'ability_recovery2' => 3,
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