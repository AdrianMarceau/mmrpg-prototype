<?
// SPEED HARDEN
$ability = array(
  'ability_name' => 'Speed Harden',
  'ability_token' => 'speed-harden',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/08/EarthSpeed',
  'ability_description' => 'The user powers up its own mobility systems with an effecient mineral program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'earth',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'hardened', 'crumbled');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>