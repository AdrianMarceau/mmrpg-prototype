<?
// DEFENSE DOUSE
$ability = array(
  'ability_name' => 'Defense Douse',
  'ability_token' => 'defense-douse',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/10/WaterDefense',
  'ability_description' => 'The user powers up its own shield systems with a defensive bubble program, raising defense by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'water',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_boost($objects, 'doused', 'drenched');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_boost($objects);

    }
  );
?>