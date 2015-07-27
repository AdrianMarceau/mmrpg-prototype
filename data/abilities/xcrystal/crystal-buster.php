<?
// CRYSTAL BUSTER
$ability = array(
  'ability_name' => 'Crystal Buster',
  'ability_token' => 'crystal-buster',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/14/Crystal',
  'ability_description' => 'The user charges itself with Crystal type energy on the first turn to increase its elemental abilities, then releases a powerful diamond shot at the target on the second to inflict massive damage!',
  'ability_type' => 'crystal',
  'ability_energy' => 4,
  'ability_damage' => 30,
  'ability_recovery2' => 33,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){

    // Call the common buster function from here
    return mmrpg_ability::ability_function_buster($objects, 'diamond', 'overwhelmed', 'beautified');

    },
  'ability_function_onload' => function($objects){

    // Call the common buster onload function from here
    return mmrpg_ability::ability_function_onload_buster($objects);

    }
  );
?>