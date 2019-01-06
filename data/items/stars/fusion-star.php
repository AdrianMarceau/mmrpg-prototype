<?
// ITEM : FUSION STAR
$item = array(
    'item_name' => 'Fusion Star',
    'item_token' => 'fusion-star',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Stars',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_value' => 9000,
    'item_description' => 'A mysterious elemental star that radiates with the energy of distant planets. These stars appear to form when two different battle fields are fused together and as a result come in a variety of types.  Collecting them may be essential to progressing through the story.  A certain character is also said to be researching these items and would likely trade an impressive amount of Zenny to study one up close.',
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Return true on success
        return true;

    }
    );
?>