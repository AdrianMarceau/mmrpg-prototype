<?
// STICKY SHOT
$ability = array(
  'ability_name' => 'Sticky Shot',
  'ability_token' => 'sticky-shot',
  'ability_group' => 'MM00',
  'ability_description' => 'The user fires a large glob of sticky glue at the target that deals damage and lowers speed by 20%!',
  'ability_damage' => 14,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>