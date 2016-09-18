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
    'item_description' => 'A mysterious chip that improves the charging capabilities of the holder.  When held by a robot master, this item allows abilities that typically require charging to instead be executed in a single turn at reduced power.',
    'item_energy' => 0,
    'item_damage2' => 2,
    'item_damage2_percent' => true,
    'item_recovery2' => 3,
    'item_recovery2_percent' => true,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>