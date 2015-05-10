<?
// ATTACK BLAST
$ability = array(
  'ability_name' => 'Attack Blast',
  'ability_token' => 'attack-blast',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/04/ExplodeAttack',
  'ability_description' => 'The user powers up its own weapon systems with a powerful bombs program, raising attack by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'explode',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_boost($objects, 'bolstered', 'shattered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_boost($objects);

    }
  );
?>