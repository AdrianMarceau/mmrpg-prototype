<?
// SPACE BUSTER
$ability = array(
  'ability_name' => 'Space Buster',
  'ability_token' => 'space-buster',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/16/Space',
  'ability_description' => 'The user charges itself with Space type energy on the first turn to increase its elemental abilities, then releases a powerful cosmic shot at the target on the second to inflict massive damage!',
  'ability_type' => 'space',
  'ability_energy' => 4,
  'ability_damage' => 30,
  'ability_recovery2' => 33,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){

    // Call the common buster function from here
    return mmrpg_ability::ability_function_buster($objects, 'cosmic', 'discorded', 'harmonized');

    },
  'ability_function_onload' => function($objects){

    // Call the common buster onload function from here
    return mmrpg_ability::ability_function_onload_buster($objects);

    }
  );
?>