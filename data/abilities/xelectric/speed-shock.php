<?
// SPEED SHOCK
$ability = array(
  'ability_name' => 'Speed Shock',
  'ability_token' => 'speed-shock',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/06/ElectricSpeed',
  'ability_description' => 'The user breaks down the target\'s mobility systems using an effecient lightning program, lowering its speed by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'electric',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_break($objects, 'shocked', 'charged');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_break($objects);

    }
  );
?>