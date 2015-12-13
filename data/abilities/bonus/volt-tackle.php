<?
// VOLT TACKLE
$ability = array(
  'ability_name' => 'Volt Tackle',
  'ability_token' => 'sticky-shot',
  'ability_group' => 'MM00',
  'ability_type' => 'electric',
  'ability_type2' => 'shield',
  'ability_description' => 'The user surrounds itself with a shield of electricity and then crashed into the foe for massive damage!',
  'ability_damage' => 14,
  'ability_accuracy' => 96,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>