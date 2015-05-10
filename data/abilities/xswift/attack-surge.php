<?
// ATTACK SURGE
$ability = array(
  'ability_name' => 'Attack Surge',
  'ability_token' => 'attack-surge',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/11/SwiftAttack',
  'ability_description' => 'The user powers up its own weapon systems with a powerful acceleration program, raising attack by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'swift',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_boost($objects, 'surged', 'stalled');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_boost($objects);

    }
  );
?>