<?
// SPEED HAMMER
$ability = array(
  'ability_name' => 'Speed Hammer',
  'ability_token' => 'speed-hammer',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/02/ImpactSpeed',
  'ability_description' => 'The user breaks down the target\'s mobility systems with an effecient punches program, lowering its speed by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'impact',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_break($objects, 'hammered', 'tempered');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_break($objects);

    }
  );
?>