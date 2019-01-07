<?
// ITEM : TARGET MODULE
$item = array(
    'item_name' => 'Target Module',
    'item_token' => 'target-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'attack',
    'item_description' => 'A mysterious chip that improves the targeting capabilities of the holder.  When held by a robot master, this item allows abilities that typically only reach the front row to target benched robots as well.',
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