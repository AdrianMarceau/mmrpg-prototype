<?
// ITEM : XTREME MODULE
$item = array(
    'item_name' => 'Xtreme Module',
    'item_token' => 'xtreme-module',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Modules2',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'energy',
    'item_description' => 'A mysterious chip that deregulates the holder\'s stats in battle.  When held by a robot master, this item makes any stat changes to its owner have an extreme effect.',
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