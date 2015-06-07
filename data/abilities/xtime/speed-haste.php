<?
// SPEED HASTE
$ability = array(
  'ability_name' => 'Speed Haste',
  'ability_token' => 'speed-haste',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/07/TimeSpeed',
  'ability_description' => 'The user powers up its own mobility systems with an effecient temporal program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'time',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'hastened', 'slowed');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>