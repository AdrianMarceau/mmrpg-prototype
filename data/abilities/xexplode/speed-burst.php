<?
// SPEED BURST
$ability = array(
  'ability_name' => 'Speed Burst',
  'ability_token' => 'speed-burst',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/04/ExplodeSpeed',
  'ability_description' => 'The user breaks down the target\'s mobility systems using an effecient bombs program, lowering its speed by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'explode',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_break($objects, 'shattered', 'bolstered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_break($objects);

    }
  );
?>