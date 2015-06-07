<?
// ATTACK CRUMBLE
$ability = array(
  'ability_name' => 'Attack Crumble',
  'ability_token' => 'attack-crumble',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/08/EarthAttack',
  'ability_description' => 'The user breaks down the target\'s weapon systems using a powerful mineral program, lowering its attack by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'earth',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_break($objects, 'crumbled', 'hardened');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_break($objects);

    }
  );
?>