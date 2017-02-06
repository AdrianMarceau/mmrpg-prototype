<?
// ITEM : ATTACK CAPSULE
$item = array(
    'item_name' => 'Attack Capsule',
    'item_token' => 'attack-capsule',
    'item_game' => 'MMRPG',
    'item_group' => 'MM00/Items/Attack',
    'item_class' => 'item',
    'item_subclass' => 'consumable',
    'item_type' => 'attack',
    'item_description' => 'A large weapon capsule that that boosts the attack stat of one robot on the user\'s side of the field by {RECOVERY}%. This item\'s effects appear to be permanent, though only up until the target has reached its max stat limit.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_recovery' => 50,
    'item_recovery_percent' => true,
    'item_accuracy' => 100,
    'item_price' => 2700,
    'item_target' => 'select_this',
    'item_function' => function($objects){

        // Call the global stat booster item function
        return rpg_item::item_function_stat_booster($objects);

        }
    );
?>