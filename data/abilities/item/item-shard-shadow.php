<?
// ITEM : SHADOW SHARD
$ability = array(
  'ability_name' => 'Shadow Shard',
  'ability_token' => 'item-shard-shadow',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Shadow',
  'ability_class' => 'item',
  'ability_subclass' => 'collectible',
  'ability_type' => 'shadow',
  'ability_description' => 'A mysterious elemental shard that radiates with the Shadow type energy of a defeated support mecha.  Collect four of these items to generate a new core that can be held by a robot master to equip Shadow type abilities or traded in at the shop for a variable amount of Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_shard($objects);
  }
  );
?>