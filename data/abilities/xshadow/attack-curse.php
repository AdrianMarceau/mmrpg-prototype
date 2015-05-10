<?
// ATTACK CURSE
$ability = array(
  'ability_name' => 'Attack Curse',
  'ability_token' => 'attack-curse',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/15/ShadowAttack',
  'ability_description' => 'The user breaks down the target\'s weapon systems using a powerful shade program, lowering its attack by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'shadow',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_break($objects, 'cursed', 'charmed');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_break($objects);

    }
  );
?>