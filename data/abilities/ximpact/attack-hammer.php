<?
// ATTACK HAMMER
$ability = array(
  'ability_name' => 'Attack Hammer',
  'ability_token' => 'attack-hammer',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/02/ImpactAttack',
  'ability_description' => 'The user breaks down the target\'s weapon systems with a powerful punches program, lowering its attack by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'impact',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_break($objects, 'hammered', 'tempered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_break($objects);

    }
  );
?>