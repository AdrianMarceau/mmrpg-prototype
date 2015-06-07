<?
// DEFENSE BLOCK
$ability = array(
  'ability_name' => 'Defense Block',
  'ability_token' => 'defense-block',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/17/ShieldDefense',
  'ability_description' => 'The user breaks down the target\'s shield systems using an anti-defensive barrier program, lowering its defense by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'shield',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_break($objects, 'disrupted', 'bolstered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_break($objects);

    }
  );
?>