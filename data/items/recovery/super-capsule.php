<?
// ITEM : SUPER CAPSULE
$item = array(
    'item_name' => 'Super Capsule',
    'item_token' => 'super-capsule',
    'item_game' => 'MMRPG',
    'item_group' => 'MM00/Items/Super',
    'item_class' => 'item',
    'item_type' => '',
    'item_type2' => 'shield',
    'item_description' => 'A large weapon capsule that that boosts the attack, defense, and speed stat of one robot on the user\'s side of the field by {RECOVERY2}% each. This item\'s effects appear to be permanent, though only up until the target has reached its max stat limits.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_recovery' => 60,
    'item_recovery_percent' => true,
    'item_recovery2' => 20,
    'item_recovery_percent' => true,
    'item_accuracy' => 100,
    'item_target' => 'select_this',
    'item_function' => function($objects){

            // Call the global stat booster item function
            return rpg_item::item_function_stat_booster($objects);

        }
    );
?>