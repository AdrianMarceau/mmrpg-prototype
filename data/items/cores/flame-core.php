<?
// ITEM : FLAME CORE
$item = array(
    'item_name' => 'Flame Core',
    'item_token' => 'flame-core',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Flame',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'flame',
    'item_description' => 'A mysterious elemental core that radiates with the Flame type energy of a defeated robot master.',
    'item_description_hold' => 'When held, this item grants compatibility with any Flame type ability as well as power and weapon energy bonuses similar to that of an internal core.',
    'item_description_shop' => 'This item appears to be coveted by a certain character and can be traded in for a variable amount of Zenny.',
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