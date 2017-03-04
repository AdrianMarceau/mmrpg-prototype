<?
// ITEM : WEAPON CODES
$item = array(
    'item_name' => 'Weapon Codes',
    'item_token' => 'weapon-codes',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/EventCodesB',
    'item_class' => 'item',
    'item_subclass' => 'event',
    'item_type' => '',
    'item_type2' => 'explode',
    'item_description' => 'A rare piece of source code acquired by Reggae during its journey, these files detail the mechanics of special weapons in the prototype and grant access to the Weapon Shop.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>