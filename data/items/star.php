<?
// ITEM : STAR
$ability = array(
  'ability_name' => 'Field Star',
  'ability_token' => 'star',
  'ability_game' => 'MM00',
  'ability_class' => 'item',
  'ability_image_sheets' => 2,
  'ability_description' => 'A strange and mysterious star-shaped energy source that takes on the elemental properties of its surroundings and glows with a radiant light. Field Stars appear in areas with high concentrations of elemental energy and have been known to fuse with each other when in close range into newer, more powerful stars.  Field Stars and Fusion Stars alike permanantly boost the elemental damage and recovery of abilities used in battle, so collecting as many as possible is a great way to ensure victory.',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

  }
  );
?>