<?
// ITEM : FORTUNE MODULE
$item = array(
    'item_name' => 'Fortune Module',
    'item_token' => 'fortune-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'electric',
    'item_description' => 'A mysterious chip that improves the holder\'s luck in battle.  When held by a robot master, this item greatly increases the amount of zenny earned from missions and causes enemies to drop items more frequently.',
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