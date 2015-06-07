<?
// SPEED BURN
$ability = array(
  'ability_name' => 'Speed Burn',
  'ability_token' => 'speed-burn',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/05/FlameSpeed',
  'ability_description' => 'The user breaks down the target\'s mobility systems using an effecient flare program, lowering its speed by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'flame',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_speed_break($objects, 'burned', 'ignited');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_speed_break($objects);

    }
  );
?>