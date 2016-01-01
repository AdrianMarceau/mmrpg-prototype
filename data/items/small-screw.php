<?
// ITEM : SMALL SCREW
$ability = array(
  'ability_name' => 'Small Screw',
  'ability_token' => 'item-screw-small',
  'ability_game' => 'MM07',
  'ability_group' => 'MM00/Items/Screws',
  'ability_class' => 'item',
  'ability_subclass' => 'treasure',
  'ability_description' => 'A small metal screw dropped by a defeated mecha.  This item is loved by a certain character and can be traded in for a moderate amount of Zenny. ',
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>