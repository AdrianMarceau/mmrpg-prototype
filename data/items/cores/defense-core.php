<?
// ITEM : DEFENSE CORE
$item = array(
    'item_name' => 'Defense Core',
    'item_token' => 'defense-core',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Defense',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'defense',
    'item_description' => 'A mysterious elemental core that radiates with the Defense type energy of a defeated robot master.',
    'item_description_shop' => 'This item appears to be coveted by a certain character and can be traded in for a variable amount of Zenny.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_value' => 3000,
    'item_target' => 'auto',
    'item_function' => function($objects){
        return rpg_item::item_function_core($objects);
    }
    );
?>