<?
// WIND BUSTER
$ability = array(
  'ability_name' => 'Wind Buster',
  'ability_token' => 'wind-buster',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/09/Wind',
  'ability_description' => 'The user charges itself with Wind type energy on the first turn to increase its elemental abilities, then releases a powerful gale shot at the target on the second to inflict massive damage!',
  'ability_type' => 'wind',
  'ability_energy' => 4,
  'ability_damage' => 30,
  'ability_recovery2' => 33,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){

    // Call the common buster function from here
    return mmrpg_ability::ability_function_buster($objects, 'gale', 'invigorated', 'disoriented');

    },
  'ability_function_onload' => function($objects){

    // Call the common buster onload function from here
    return mmrpg_ability::ability_function_onload_buster($objects);

    }
  );
?>