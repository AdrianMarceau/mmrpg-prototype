<?
// ITEM : WEAPON UPGRADE
$item = array(
    'item_name' => 'Weapon Upgrade',
    'item_token' => 'weapon-upgrade',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Upgrades',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'weapons',
    'item_description' => 'A mysterious drive containing some kind of weapon upgrade program.  When held by a robot master, this item doubles the user\'s maximum weapon energy in battle.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_price' => 32000,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'item_flag_unlockable' => true
    );
?>