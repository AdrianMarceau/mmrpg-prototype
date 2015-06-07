<?
// SPEED TEMPER
$ability = array(
  'ability_name' => 'Speed Temper',
  'ability_token' => 'speed-temper',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/02/ImpactSpeed',
  'ability_description' => 'The user powers up its own mobility systems with an effecient punches program, raising speed by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 20,
  'ability_recovery_percent' => true,
  'ability_type' => 'impact',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_boost($objects, 'tempered', 'hammered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_boost($objects);

    }
  );
?>