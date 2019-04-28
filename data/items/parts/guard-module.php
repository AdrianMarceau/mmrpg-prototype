<?
// ITEM : GUARD MODULE
$item = array(
    'item_name' => 'Guard Module',
    'item_token' => 'guard-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'explode',
    'item_description' => 'A mysterious chip that protects the holder\'s stats in battle.  When held by a robot master, this item prevents all stat boosts, breaks, and swaps from affecting the user.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_price' => 8000,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>