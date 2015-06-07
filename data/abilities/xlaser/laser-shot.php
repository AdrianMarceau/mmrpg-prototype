<?
// LASER SHOT
$ability = array(
  'ability_name' => 'Laser Shot',
  'ability_token' => 'laser-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/18/Laser',
  'ability_description' => 'The user fires a small beam shot at the target to inflict Laser type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'laser',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'beam', 'burned', 'healed');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>