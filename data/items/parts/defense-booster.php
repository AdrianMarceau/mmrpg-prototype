<?
// ITEM : DEFENSE BOOSTER
$item = array(
    'item_name' => 'Defense Booster',
    'item_token' => 'defense-booster',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/StatBoosters',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'defense',
    'item_description' => 'A mysterious disc containing some kind of defense booster program.  When held by a robot master, this item increases the user\'s defense stat by {RECOVERY2}% at the end of each turn in battle.',
    'item_energy' => 0,
    'item_recovery2' => 10,
    'item_recovery2_percent' => true,
    'item_speed' => 10,
    'item_price' => 32000,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>