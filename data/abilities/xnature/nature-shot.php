<?
// NATURE SHOT
$ability = array(
  'ability_name' => 'Nature Shot',
  'ability_token' => 'nature-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/12/Nature',
  'ability_description' => 'The user fires a small solar shot at the target to inflict Nature type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'nature',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'solar', 'burned', 'relaxed');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>