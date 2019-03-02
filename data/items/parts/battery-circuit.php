<?
// ITEM : BATTERY CIRCUIT
$item = array(
    'item_name' => 'Battery Circuit',
    'item_token' => 'battery-circuit',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Circuits',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => '',
    'item_type2' => 'electric',
    'item_description' => 'A mysterious circuit that grants the holder an elemental affinity to Electric types in battle.  When held by a robot master, this item automatically converts all Electric type damage into Life Energy instead.',
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