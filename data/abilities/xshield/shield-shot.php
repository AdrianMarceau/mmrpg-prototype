<?
// SHIELD SHOT
$ability = array(
  'ability_name' => 'Shield Shot',
  'ability_token' => 'shield-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/17/Shield',
  'ability_description' => 'The user fires a small barrier shot at the target to inflict Shield type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'shield',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'barrier', 'disrupted', 'bolstered');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>