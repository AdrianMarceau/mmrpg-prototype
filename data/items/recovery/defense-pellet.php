<?
// ITEM : DEFENSE PELLET
$item = array(
    'item_name' => 'Defense Pellet',
    'item_token' => 'defense-pellet',
    'item_game' => 'MMRPG',
    'item_group' => 'MM00/Items/Defense',
    'item_class' => 'item',
    'item_subclass' => 'consumable',
    'item_type' => 'defense',
    'item_description' => 'A small shield pellet that boosts the defense stat of one robot on the user\'s side of the field by up to {RECOVERY}% its base value.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_recovery' => 25,
    'item_recovery_percent' => true,
    'item_accuracy' => 100,
    'item_price' => 900,
    'item_target' => 'select_this',
    'item_function' => function($objects){

            // Call the global stat booster item function
            return rpg_item::item_function_stat_booster($objects);

        }
    );
?>