<?
// ITEM : SHARE PROGRAM
$item = array(
    'item_name' => 'Share Program',
    'item_token' => 'share-program',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Events',
    'item_class' => 'item',
    'item_subclass' => 'event',
    'item_type' => '',
    'item_type2' => 'light',
    'item_description' => 'A helpful program developed by Dr. Light for use in the prototype, this item allows the doctors to share their unlocked abilities and items with one another.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>