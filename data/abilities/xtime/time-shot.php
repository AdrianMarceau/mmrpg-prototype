<?
// TIME SHOT
$ability = array(
  'ability_name' => 'Time Shot',
  'ability_token' => 'time-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/07/Time',
  'ability_description' => 'The user fires a small temporal shot at the target to inflict Time type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'time',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'temporal', 'warped', 'synced');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>