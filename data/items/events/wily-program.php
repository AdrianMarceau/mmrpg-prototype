<?
// ITEM : WILY PROGRAM
$item = array(
    'item_name' => 'Wily Program',
    'item_token' => 'wily-program',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/EventPrograms',
    'item_class' => 'item',
    'item_subclass' => 'event',
    'item_type' => '',
    'item_type2' => 'wily',
    'item_description' => 'An essential program developed by Dr. Wily for use in the prototype, this item allows the doctors to transfer unlocked robot masters between each other.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>