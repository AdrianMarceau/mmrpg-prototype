<?
// ITEM : DEFENSE SHARD
$item = array(
    'item_name' => 'Defense Shard',
    'item_token' => 'defense-shard',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Defense',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_type' => 'defense',
    'item_description' => 'A mysterious elemental shard that radiates with the Defense type energy of a defeated support mecha.  Collect four of these items to generate a new core!',
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