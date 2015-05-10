<?
// ATTACK DOUSE
$ability = array(
  'ability_name' => 'Attack Douse',
  'ability_token' => 'attack-douse',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/10/WaterAttack',
  'ability_description' => 'The user powers up its own weapon systems with a powerful bubble program, raising attack by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'water',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_boost($objects, 'doused', 'drenched');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_boost($objects);

    }
  );
?>