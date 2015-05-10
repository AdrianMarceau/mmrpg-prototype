<?
// DEFENSE BLAST
$ability = array(
  'ability_name' => 'Defense Blast',
  'ability_token' => 'defense-blast',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/04/ExplodeDefense',
  'ability_description' => 'The user powers up its own shield systems with a defensive bombs program, raising defense by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'explode',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_boost($objects, 'bolstered', 'shattered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_boost($objects);

    }
  );
?>