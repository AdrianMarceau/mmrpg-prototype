<?
// ATTACK TARNISH
$ability = array(
  'ability_name' => 'Attack Tarnish',
  'ability_token' => 'attack-tarnish',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/14/CrystalAttack',
  'ability_description' => 'The user breaks fown the target\'s weapon systems using a powerful diamond program, lowering its attack by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'crystal',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_break($objects, 'blemished', 'beautified');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_break($objects);

    }
  );
?>