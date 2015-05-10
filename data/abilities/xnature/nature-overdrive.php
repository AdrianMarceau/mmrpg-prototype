<?
// NATURE OVERDRIVE
$ability = array(
  'ability_name' => 'Nature Overdrive',
  'ability_token' => 'nature-overdrive',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/12/Nature',
  'ability_description' => 'The user releases all of their stored weapon energy at once in a powerful storm of solar shots, dealing Nature type damage to all targets on the opponent\'s side of the field.  This ability\'s power is directly proportionate to the amount of life energy the user has lost, making it most effective when used in critical condition.',
  'ability_type' => 'nature',
  'ability_energy' => 0,
  'ability_energy_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common overdrive function from here
    return mmrpg_ability::ability_function_overdrive($objects, 'solar', 'incinerated', 'invigorated');

    },
  'ability_function_onload' => function($objects){

    // Call the common overdrive onload function from here
    return mmrpg_ability::ability_function_onload_overdrive($objects);

    }
  );
?>