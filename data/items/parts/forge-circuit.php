<?
// ITEM : FORGE CIRCUIT
$item = array(
    'item_name' => 'Forge Circuit',
    'item_token' => 'forge-circuit',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Circuits',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'flame',
    'item_description' => 'A mysterious forge-like circuit that grants the holder an elemental affinity to Flame type damage in exchange for a weakness to Water type damage.',
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