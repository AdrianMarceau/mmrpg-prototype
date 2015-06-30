<?
// ITEM : ATTACK SHARD
$ability = array(
  'ability_name' => 'Attack Shard',
  'ability_token' => 'item-shard-attack',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Items/Attack',
  'ability_class' => 'item',
  'ability_subclass' => 'collectible',
  'ability_type' => 'attack',
  'ability_description' => 'A mysterious elemental shard that radiates with the Attack type energy of a defeated support mecha.  These items have no effect in battle, but collecting five of them will generate a new core that can be traded in for Zenny.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_shard($objects);
  }
  );
?>