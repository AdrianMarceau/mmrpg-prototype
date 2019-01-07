<?
// ITEM : CHARGE MODULE
$item = array(
    'item_name' => 'Charge Module',
    'item_token' => 'charge-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'defense',
    'item_description' => 'A mysterious chip that improves the charging capabilities of the holder.  When held by a robot master, this item allows the user to skip the charging phase of abilities that typically require two turns to execute.',
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