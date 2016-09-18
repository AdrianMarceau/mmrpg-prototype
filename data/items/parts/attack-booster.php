<?
// ITEM : ATTACK BOOSTER
$item = array(
    'item_name' => 'Attack Booster',
    'item_token' => 'attack-booster',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/StatBoosters',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'attack',
    'item_description' => 'A mysterious disc containing some kind of attack booster program.  When held by a robot master, this item increases the user\'s attack stat by {RECOVERY2}% at end of each turn in battle.',
    'item_energy' => 0,
    'item_recovery2' => 10,
    'item_recovery2_percent' => true,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>