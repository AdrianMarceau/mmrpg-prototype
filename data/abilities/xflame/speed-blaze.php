<?
// SPEED BLAZE
$ability = array(
  'ability_name' => 'Speed Blaze',
  'ability_token' => 'speed-blaze',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/05/FlameSpeed',
  'ability_description' => 'The user powers up its own mobility systems with an effecient flare program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'flame',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'ignited', 'burned');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>