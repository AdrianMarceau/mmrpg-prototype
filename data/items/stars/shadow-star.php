<?
// ITEM : SHADOW STAR
$item = array(
    'item_name' => 'Shadow Star',
    'item_token' => 'shadow-star',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Shadow',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'shadow',
    'item_description' => 'A mysterious elemental star that radiates with the Shadow type energy of a distant planet. Collecting a single one of these items permanently grants +10% toward all Shadow type damage and recovery.  A certain character is said to be researching these items and would likely trade a respectable amount of Zenny to study one up close.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_price' => 6000,
    'item_target' => 'auto',
    'item_function' => function($objects){
        return rpg_item::item_function_core($objects);
    }
    );
?>