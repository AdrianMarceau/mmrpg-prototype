<?
// FLAME SHOT
$ability = array(
  'ability_name' => 'Flame Shot',
  'ability_token' => 'flame-shot',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/05/Flame',
  'ability_description' => 'The user fires a small fire shot at the target to inflict Flame type damage. This ability\'s power increases if the user if holding a buster charge of the same element.',
  'ability_type' => 'flame',
  'ability_energy' => 0,
  'ability_damage' => 20,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common shot function from here
    return mmrpg_ability::ability_function_shot($objects, 'fire', 'burned', 'ignited');

    },
  'ability_function_onload' => function($objects){

    // Call the common shot onload function from here
    return mmrpg_ability::ability_function_onload_shot($objects);

    }
  );
?>