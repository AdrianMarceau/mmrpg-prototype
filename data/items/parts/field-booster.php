<?
// ITEM : FIELD BOOSTER
$item = array(
    'item_name' => 'Field Booster',
    'item_token' => 'field-booster',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/StatBoosters',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'copy',
    'item_description' => 'A mysterious disc containing some kind of elemental booster program.  When held by a robot master, this item increases the field multiplier matching the user\'s core type by up to {RECOVERY2}% at end of each turn in battle.',
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