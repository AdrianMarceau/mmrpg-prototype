<?
// ITEM : NATURE CORE
$ability = array(
  'ability_name' => 'Nature Core',
  'ability_token' => 'item-core-nature',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Nature',
  'ability_class' => 'item',
  'ability_type' => 'nature',
  'ability_description' => 'A mysterious elemental core that radiates with the Nature type energy of a defeated robot master.  This item can be thrown in battle to deal massive Nature type damage to the target, but it is also coveted by a certain character and can be traded in for a variable amount of Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_core($objects);
  }
  );
?>