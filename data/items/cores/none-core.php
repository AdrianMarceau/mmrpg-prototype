<?
// ITEM : NEUTRAL CORE
$item = array(
    'item_name' => 'Neutral Core',
    'item_token' => 'none-core',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Neutral',
    'item_class' => 'item',
    'item_subclass' => 'treasure',
    'item_type' => '',
    'item_description' => 'A mysterious elemental core that radiates with the Neutral type energy of a defeated robot master.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_damage' => 0,
    'item_accuracy' => 100,
    'item_value' => 6000,
    'item_target' => 'select_target',
    'item_function' => function($objects){
        return rpg_item::item_function_core($objects);
    },
    'item_function_onload' => function($objects){
        return rpg_item::item_function_onload_core($objects);
    }
    );
?>