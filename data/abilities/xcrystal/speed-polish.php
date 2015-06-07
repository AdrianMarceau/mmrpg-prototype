<?
// SPEED POLISH
$ability = array(
  'ability_name' => 'Speed Polish',
  'ability_token' => 'speed-polish',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/14/CrystalSpeed',
  'ability_description' => 'The user powers up its own mobility systems with an effecient diamond program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'crystal',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'beautified', 'blemished');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>