<?
// ITEM : FIELD STAR
$item = array(
    'item_name' => 'Field Star',
    'item_token' => 'field-star',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Stars',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_value' => 4000,
    'item_description' => 'A mysterious elemental star that radiates with the energy of a distant planet. These stars appear to form on elemental battle fields and come in a variety of different types. Collecting stars increases one\'s Starforce and makes their robots grow stronger!',
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Return true on success
        return true;

    }
    );
?>