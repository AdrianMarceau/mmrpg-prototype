<?
// ITEM : FUSION STAR
$item = array(
    'item_name' => 'Fusion Star',
    'item_token' => 'fusion-star',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Stars',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_value' => 8000,
    'item_description' => 'A mysterious elemental star that radiates with the energy of distant planets. These stars appear to form when two different battle fields fuse together and come in a variety of different types. Collecting stars increases one\'s Starforce and makes their robots grow stronger!',
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Return true on success
        return true;

    }
    );
?>