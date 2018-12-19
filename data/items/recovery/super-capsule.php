<?
// ITEM : SUPER CAPSULE
$item = array(
    'item_name' => 'Super Capsule',
    'item_token' => 'super-capsule',
    'item_game' => 'MMRPG',
    'item_group' => 'MM00/Items/Super',
    'item_class' => 'item',
    'item_subclass' => 'consumable',
    'item_type' => '',
    'item_type2' => 'shield',
    'item_description' => 'A large multi-color capsule that drastically raises the attack, defense, and speed stats of one robot on the user\'s side of the field.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_recovery' => 9,
    'item_accuracy' => 100,
    'item_price' => 10800,
    'item_target' => 'select_this',
    'item_function' => function($objects){

        // Call the global stat booster item function
        return rpg_item::item_function_stat_booster($objects);

        }
    );
?>