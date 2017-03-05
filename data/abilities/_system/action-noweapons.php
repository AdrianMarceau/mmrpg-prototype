<?
// ACTION : NO WEAPONS
$ability = array(
  'ability_name' => 'Waiting',
  'ability_token' => 'action-noweapons',
  'ability_class' => 'system',
  'ability_description' => 'Critically low on weapon energy and unable to use an ability, the active robot waits to recharge for one turn...',
  'ability_energy' => 0,
  'ability_damage' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;

    }
  );
?>