<?
// DEFENSE BREEZE
$ability = array(
  'ability_name' => 'Defense Breeze',
  'ability_token' => 'defense-breeze',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/09/WindDefense',
  'ability_description' => 'The user powers up its own shield systems with a defensive zephyr program, raising defense by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'wind',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_boost($objects, 'refreshed', 'disrupted');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_boost($objects);

    }
  );
?>