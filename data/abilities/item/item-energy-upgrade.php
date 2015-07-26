<?
// ITEM : ENERGY UPGRADE
$ability = array(
  'ability_name' => 'Energy Upgrade',
  'ability_token' => 'item-energy-upgrade',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Upgrades',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'energy',
  'ability_description' => 'A mysterious drive containing some kind of energy upgrade program.  When held by a robot master, this item doubles the user\'s maximum life energy in battle.',
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