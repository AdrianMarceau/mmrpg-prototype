<?
// SPEED CHAOS
$ability = array(
  'ability_name' => 'Speed Chaos',
  'ability_token' => 'speed-chaos',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/16/SpaceSpeed',
  'ability_description' => 'The user breaks down the target\'s mobility systems using an effecient chaotic energy program, lowering its speed by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'space',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_break($objects, 'discorded', 'harmonized');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_break($objects);

    }
  );
?>