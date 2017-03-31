<?
// ITEM : COPY CORE
$item = array(
    'item_name' => 'Copy Core',
    'item_token' => 'copy-core',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Copy',
    'item_class' => 'item',
    'item_subclass' => 'treasure',
    'item_type' => 'copy',
    'item_description' => 'A mysterious elemental core that radiates with the Copy type energy of a defeated robot master.',
    'item_description_shop' => 'This item appears to be coveted by a certain character and can be traded in for a variable amount of Zenny.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_damage' => 0,
    'item_accuracy' => 100,
    'item_price' => 6000,
    'item_target' => 'select_target',
    'item_function' => function($objects){
        return rpg_item::item_function_core($objects);
    },
    'item_function_onload' => function($objects){
        return rpg_item::item_function_onload_core($objects);
    },
    'item_flag_unlockable' => true
    );
?>