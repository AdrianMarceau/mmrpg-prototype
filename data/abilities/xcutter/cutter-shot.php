<?
// CUTTER SHOT
$ability = array(
  'ability_name' => 'Cutter Shot',
  'ability_token' => 'cutter-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/01/Cutter',
  'ability_description' => 'The user fires a small blade shot at the target to inflict Cutter type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'cutter',
  'ability_energy' => 0,
  'ability_damage' => 10,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'blade', 'cut through', 'excited');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>