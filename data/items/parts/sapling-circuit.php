<?
// ITEM : SAPLING CIRCUIT
$item = array(
    'item_name' => 'Sapling Circuit',
    'item_token' => 'sapling-circuit',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Circuits',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'nature',
    'item_description' => 'A mysterious sapling-like circuit that grants the holder an elemental affinity to Nature type damage in exchange for a weakness to Flame type damage.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_price' => 8500,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>