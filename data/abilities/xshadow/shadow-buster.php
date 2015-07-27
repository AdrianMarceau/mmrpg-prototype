<?
// SHADOW BUSTER
$ability = array(
  'ability_name' => 'Shadow Buster',
  'ability_token' => 'shadow-buster',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/15/Shadow',
  'ability_description' => 'The user charges itself with Shadow type energy on the first turn to increase its elemental abilities, then releases a powerful shade shot at the target on the second to inflict massive damage!',
  'ability_type' => 'shadow',
  'ability_energy' => 4,
  'ability_damage' => 30,
  'ability_recovery2' => 33,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){

    // Call the common buster function from here
    return mmrpg_ability::ability_function_buster($objects, 'shade', 'horrified', 'enchanted');

    },
  'ability_function_onload' => function($objects){

    // Call the common buster onload function from here
    return mmrpg_ability::ability_function_onload_buster($objects);

    }
  );
?>