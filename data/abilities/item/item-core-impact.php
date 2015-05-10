<?
// ITEM : IMPACT CORE
$ability = array(
  'ability_name' => 'Impact Core',
  'ability_token' => 'item-core-impact',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Impact',
  'ability_class' => 'item',
  'ability_type' => 'impact',
  'ability_description' => 'A mysterious elemental core that radiates with the Impact type energy of a defeated robot master.  This item can be thrown in battle to deal massive Impact type damage to the target, but it is also coveted by a certain character and can be traded in for a variable amount of Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_core($objects);
  }
  );
?>