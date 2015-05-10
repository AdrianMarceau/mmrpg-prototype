<?
// ATTACK FADE
$ability = array(
  'ability_name' => 'Attack Fade',
  'ability_token' => 'attack-fade',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/18/LaserAttack',
  'ability_description' => 'The user breaks down the target\'s weapons using a powerful beams program, lowering its attack by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'laser',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_attack_break($objects, 'faded', 'glowed');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_attack_break($objects);

    }
  );
?>