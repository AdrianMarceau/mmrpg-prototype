<?
// CRYSTAL SHOT
$ability = array(
  'ability_name' => 'Crystal Shot',
  'ability_token' => 'crystal-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/14/Crystal',
  'ability_description' => 'The user fires a small diamond shot at the target to inflict Crystal type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'crystal',
  'ability_energy' => 0,
  'ability_damage' => 10,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'diamond', 'overwhelmed', 'beautified');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>