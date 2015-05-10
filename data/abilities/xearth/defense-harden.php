<?
// DEFENSE HARDEN
$ability = array(
  'ability_name' => 'Defense Harden',
  'ability_token' => 'defense-harden',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/08/EarthDefense',
  'ability_description' => 'The user powers up its own shield systems with a defensive mineral program, raising defense by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'earth',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_boost($objects, 'hardened', 'crumbled');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_boost($objects);

    }
  );
?>