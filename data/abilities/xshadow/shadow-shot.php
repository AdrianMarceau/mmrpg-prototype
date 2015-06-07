<?
// SHADOW SHOT
$ability = array(
  'ability_name' => 'Shadow Shot',
  'ability_token' => 'shadow-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/15/Shadow',
  'ability_description' => 'The user fires a small shade shot at the target to inflict Shadow type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'shadow',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'shade', 'spooked', 'thrilled');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>