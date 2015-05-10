<?
// SPEED BLOCK
$ability = array(
  'ability_name' => 'Speed Block',
  'ability_token' => 'speed-block',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/17/ShieldSpeed',
  'ability_description' => 'The user breaks down the target\'s mobility systems using an effecient barrier program, lowering its speed by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'shield',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_break($objects, 'disrupted', 'bolstered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_break($objects);

    }
  );
?>