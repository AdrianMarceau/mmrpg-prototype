<?
// EXPLODE SHOT
$ability = array(
  'ability_name' => 'Explode Shot',
  'ability_token' => 'explode-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/04/Explode',
  'ability_description' => 'The user fires a small explosive shot at the target to inflict Explode type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'explode',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'explosive', 'blasted', 'refreshed');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>