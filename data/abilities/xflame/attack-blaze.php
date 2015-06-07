<?
// ATTACK BLAZE
$ability = array(
  'ability_name' => 'Attack Blaze',
  'ability_token' => 'attack-blaze',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/05/FlameAttack',
  'ability_description' => 'The user powers up its own weapon systems with a powerful flare program, raising attack by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'flame',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_boost($objects, 'ignited', 'burned');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_boost($objects);

    }
  );
?>