<?
// ITEM : GROWTH MODULE
$item = array(
    'item_name' => 'Growth Module',
    'item_token' => 'growth-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'energy',
    'item_description' => 'A mysterious chip that improves the holder\'s experiences in battle.  When held by a robot master, this item doubles experience points and bonus stats earned upon defeating a target.',
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