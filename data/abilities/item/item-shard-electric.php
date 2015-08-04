<?
// ITEM : ELECTRIC SHARD
$ability = array(
  'ability_name' => 'Electric Shard',
  'ability_token' => 'item-shard-electric',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Electric',
  'ability_class' => 'item',
  'ability_subclass' => 'collectible',
  'ability_type' => 'electric',
  'ability_description' => 'A mysterious elemental shard that radiates with the Electric type energy of a defeated support mecha.  Collect four of these items to generate a new core that can be held by a robot master to equip Electric type abilities or traded in at the shop for a variable amount of Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_shard($objects);
  }
  );
?>