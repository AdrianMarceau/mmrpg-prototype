<?
// EARTH OVERDRIVE
$ability = array(
  'ability_name' => 'Earth Overdrive',
  'ability_token' => 'earth-overdrive',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/08/Earth',
  'ability_description' => 'The user releases all of their stored weapon energy at once in a powerful storm of quake shots, dealing massive Earth type damage to all targets on the opponents\' side of the field!',
  'ability_type' => 'earth',
  'ability_energy' => 0,
  'ability_energy_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common overdrive function from here
    return mmrpg_ability::ability_function_overdrive($objects, 'quake', 'crumbled', 'hardened');

    },
  'ability_function_onload' => function($objects){

    // Call the common overdrive onload function from here
    return mmrpg_ability::ability_function_onload_overdrive($objects);

    }
  );
?>