<?
// ITEM : AUTO LINK
$item = array(
    'item_name' => 'Auto Link',
    'item_token' => 'auto-link',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/EventLinks',
    'item_class' => 'item',
    'item_subclass' => 'event',
    'item_type' => '',
    'item_type2' => 'nature',
    'item_description' => 'A communication link created by Dr. Light for use inside the prototype, this item grants the doctors contact with Auto and allows access to his shop.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>