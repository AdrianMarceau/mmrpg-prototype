<?
// SPEED GROWTH
$ability = array(
  'ability_name' => 'Speed Growth',
  'ability_token' => 'speed-growth',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/12/NatureSpeed',
  'ability_description' => 'The user powers up its own mobility systems using an effecient plants program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'nature',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'vitalized', 'drained');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>