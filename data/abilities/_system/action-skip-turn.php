<?
// ACTION : SKIP TURN
$ability = array(
  'ability_name' => 'Skip Turn',
  'ability_token' => 'action-skip-turn',
  'ability_class' => 'system',
  'ability_description' => 'The user skips their turn and allows the opponent to go instead, either strategically or out of desperation.',
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