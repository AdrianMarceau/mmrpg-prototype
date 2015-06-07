<?
// SPEED GUARD
$ability = array(
  'ability_name' => 'Speed Guard',
  'ability_token' => 'speed-guard',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/17/ShieldSpeed',
  'ability_description' => 'The user powers up its own mobility systems with an effecient barrier program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'shield',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'bolstered', 'disrupted');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>