<?
// ATTACK DRENCH
$ability = array(
  'ability_name' => 'Attack Drench',
  'ability_token' => 'attack-drench',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/10/WaterAttack',
  'ability_description' => 'The user breaks down the target\'s weapons using a powerful bubble program, lowering its attack by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'water',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_break($objects, 'drenched', 'doused');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_break($objects);

    }
  );
?>