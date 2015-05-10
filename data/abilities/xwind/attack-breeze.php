<?
// ATTACK BREEZE
$ability = array(
  'ability_name' => 'Attack Breeze',
  'ability_token' => 'attack-breeze',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/09/WindAttack',
  'ability_description' => 'The user powers up its own weapon systems with a powerful zephyr program, raising attack by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'wind',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_boost($objects, 'refreshed', 'disrupted');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_boost($objects);

    }
  );
?>