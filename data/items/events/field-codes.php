<?
// ITEM : FIELD CODES
$item = array(
    'item_name' => 'Field Codes',
    'item_token' => 'field-codes',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/EventCodesC',
    'item_class' => 'item',
    'item_subclass' => 'event',
    'item_type' => '',
    'item_type2' => 'electric',
    'item_description' => 'A rare piece of source code acquired by Kalinka during her journey, these files detail the mechanics of battle fields in the prototype and grant access to the Field Shop.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'item_flag_unlockable' => true
    );
?>