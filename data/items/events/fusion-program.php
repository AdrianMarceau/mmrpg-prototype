<?
// ITEM : FUSION PROGRAM
$item = array(
    'item_name' => 'Fusion Program',
    'item_token' => 'fusion-program',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Events',
    'item_class' => 'item',
    'item_subclass' => 'event',
    'item_type' => '',
    'item_type2' => 'cossack',
    'item_description' => 'A mysterious program developed by Dr. Cossack for use in the prototype, this item allows the doctors to customize the order of their battle fields for new fusions.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_function' => function($objects){

        // Return true on success
        return true;

    }
    );
?>