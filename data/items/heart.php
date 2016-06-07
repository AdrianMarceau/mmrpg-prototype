<?
// ITEM : HEART TANK
$item = array(
  'item_name' => 'Heart Tank',
  'item_token' => 'heart',
  'item_game' => 'MM00',
  'item_class' => 'item',
  'item_description' => 'A rare, heart-shaped life tank that increases the base life energy of all robots on this player\'s side of the field at the start of battle.  The boosting power of this item is divided evenly among all teammates, so bringing too many robots into battle may reduce it\'s overall impact.  Note that the effects of this item can be stacked if the user owns multiple copies, so try to collect as many as possible.',
  'item_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

  }
  );
?>