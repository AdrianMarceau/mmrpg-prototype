<?
// DEFENSE POLISH
$ability = array(
  'ability_name' => 'Defense Polish',
  'ability_token' => 'defense-polish',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/14/CrystalDefense',
  'ability_description' => 'The user powers up its own shield systems with a defensive diamond program, raising defense by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'crystal',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_boost($objects, 'beautified', 'blemished');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_boost($objects);

    }
  );
?>