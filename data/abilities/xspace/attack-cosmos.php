<?
// ATTACK COSMOS
$ability = array(
  'ability_name' => 'Attack Cosmos',
  'ability_token' => 'attack-cosmos',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/16/SpaceAttack',
  'ability_description' => 'The user powers up its own weapon systems with a powerful cosmic energy program, raising attack by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'space',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_boost($objects, 'harmonized', 'discorded');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_boost($objects);

    }
  );
?>