<?
// ITEM : EMPTY SHARD
$item = array(
    'item_name' => 'Empty Shard',
    'item_token' => 'empty-shard',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Empty',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_type' => 'empty',
    'item_description' => 'A mysterious elemental shard that radiates with the Empty type energy of a defeated support mecha.  These items have no effect in battle, but collecting five of them will generate a new core that can be traded in for Zenny.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_value' => 750,
    'item_target' => 'auto',
    'item_function' => function($objects){
        return rpg_item::item_function_shard($objects);
    }
    );
?>