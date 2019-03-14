<?
// ITEM : MISSILE SHARD
$item = array(
    'item_name' => 'Missile Shard',
    'item_token' => 'missile-shard',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Missile',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_type' => 'missile',
    'item_description' => 'A mysterious elemental shard that radiates with the Missile type energy of a defeated support mecha.  Collect four of these items to generate a new core!',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_value' => 0,
    'item_target' => 'auto',
    'item_function' => function($objects){
        return rpg_item::item_function_shard($objects);
    }
    );
?>