<?
// ITEM : ATTACK CORE
$ability = array(
  'ability_name' => 'Attack Core',
  'ability_token' => 'item-core-attack',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Attack',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'attack',
  'ability_description' => 'A mysterious elemental core that radiates with the Attack type energy of a defeated robot master.  These items have no effect in battle, but are loved by a certain character and can be traded in for a respectable amount of Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_core($objects);
  }
  );
?>