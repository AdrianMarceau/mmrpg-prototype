<?
// SPEED GLOW
$ability = array(
  'ability_name' => 'Speed Glow',
  'ability_token' => 'speed-glow',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/18/LaserSpeed',
  'ability_description' => 'The user powers up its own mobility systems with an effecient beams program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'laser',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'glowed', 'faded');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>