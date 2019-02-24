<?
// ITEM : REVERSE MODULE
$item = array(
    'item_name' => 'Reverse Module',
    'item_token' => 'reverse-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'speed',
    'item_description' => 'A mysterious chip that regulates the holder\'s stats in battle.  When held by a robot master, this item makes any stat changes to the user have an opposite effect.',
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