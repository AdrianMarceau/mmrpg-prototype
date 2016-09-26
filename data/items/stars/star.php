<?
// ITEM : STAR
$item = array(
    'item_name' => 'Field Star',
    'item_token' => 'star',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Stars',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_image_sheets' => 2,
    'item_description' => 'A mysterious elemental star that radiates with the energy of a distant planet. These stars appear to come in a variety of different types and collecting lots of them may be essential to progressing through the story.  A certain character is also said to be researching these items and would likely trade a respectable amount of Zenny to study one up close.',
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Return true on success
        return true;

    }
    );
?>