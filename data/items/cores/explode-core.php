<?
// ITEM : EXPLODE CORE
$item = array(
    'item_name' => 'Explode Core',
    'item_token' => 'explode-core',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Explode',
    'item_class' => 'item',
    'item_type' => 'explode',
    'item_description' => 'A mysterious elemental core that radiates with the Explode type energy of a defeated robot master. When used in battle, this item can be thrown at opposing targets to deal Explode type damage with a base power equal to the user\'s current level. This item is also coveted by a certain character and can be traded in for a variable amount of Zenny.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_damage' => 0,
    'item_accuracy' => 100,
    'item_price' => 3000,
    'item_target' => 'select_target',
    'item_function' => function($objects){
        return rpg_item::item_function_core($objects);
    },
    'item_function_onload' => function($objects){
        return rpg_item::item_function_onload_core($objects);
    }
    );
?>