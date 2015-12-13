<?
// STICKY BOND
$ability = array(
  'ability_name' => 'Sticky Bond',
  'ability_token' => 'sticky-bond',
  'ability_group' => 'MM00',
  'ability_description' => 'The user attaches a large glob of sticky glue to the target that deals damage and prevents switching for three turns!',
  'ability_damage' => 8,
  'ability_accuracy' => 94,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>