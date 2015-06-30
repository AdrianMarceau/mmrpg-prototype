<?
// ITEM : SPEED STAR
$ability = array(
  'ability_name' => 'Speed Star',
  'ability_token' => 'item-star-speed',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Speed',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'speed',
  'ability_description' => 'A mysterious elemental star that radiates with the Speed type energy of a distant planet.  These items have no effect in battle, but are loved by a certain character and can be traded in for an impressive amount of Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_core($objects);
  }
  );
?>