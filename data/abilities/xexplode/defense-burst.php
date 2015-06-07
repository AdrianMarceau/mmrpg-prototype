<?
// DEFENSE BURST
$ability = array(
  'ability_name' => 'Defense Burst',
  'ability_token' => 'defense-burst',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/04/ExplodeDefense',
  'ability_description' => 'The user breaks down the target\'s shields using a defensive bombs program, lowering its defense by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'explode',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_break($objects, 'shattered', 'bolstered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_break($objects);

    }
  );
?>