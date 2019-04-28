<?
// ITEM : LOCKING MODULE
$item = array(
    'item_name' => 'Locking Module',
    'item_token' => 'locking-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'explode',
    'item_description' => 'A mysterious chip that regulates the holder\'s stats in battle.  When held by a robot master, this item prevents any and all stat changes from affecting the user.',
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