<?
// ITEM : SPEED CORE
$item = array(
    'item_name' => 'Speed Core',
    'item_token' => 'speed-core',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Speed',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'speed',
    'item_description' => 'A mysterious elemental core that radiates with the Speed type energy of a defeated robot master.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_value' => 3000,
    'item_target' => 'auto',
    'item_function' => function($objects){
        return rpg_item::item_function_core($objects);
    }
    );
?>