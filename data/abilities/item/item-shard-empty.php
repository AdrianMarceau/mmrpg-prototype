<?
// ITEM : EMPTY SHARD
$ability = array(
  'ability_name' => 'Empty Shard',
  'ability_token' => 'item-shard-empty',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Empty',
  'ability_class' => 'item',
  'ability_subclass' => 'collectible',
  'ability_type' => 'empty',
  'ability_description' => 'A mysterious elemental shard that radiates with the Empty type energy of a defeated support mecha.  These items have no effect in battle, but collecting five of them will generate a new core that can be traded in for Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_shard($objects);
  }
  );
?>