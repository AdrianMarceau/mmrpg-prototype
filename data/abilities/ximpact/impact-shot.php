<?
// IMPACT SHOT
$ability = array(
  'ability_name' => 'Impact Shot',
  'ability_token' => 'impact-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/02/Impact',
  'ability_description' => 'The user fires a large weighted shot at the target to inflict Impact type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'impact',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'weighted', 'weakened', 'strengthened');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>