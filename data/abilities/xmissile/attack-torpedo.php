<?
// ATTACK TORPEDO
$ability = array(
  'ability_name' => 'Attack Torpedo',
  'ability_token' => 'attack-torpedo',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/13/MissileAttack',
  'ability_description' => 'The user breaks down the target\'s weapon systems using a powerful sniper program, lowering its attack by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'missile',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_break($objects, 'pierced', 'excited');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_break($objects);

    }
  );
?>