<?
// DEFENSE TORPEDO
$ability = array(
  'ability_name' => 'Defense Torpedo',
  'ability_token' => 'defense-torpedo',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/13/MissileDefense',
  'ability_description' => 'The user breaks down the target\'s shield systems using an anti-defensive sniper program, lowering its defense by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 20,
  'ability_damage_percent' => true,
  'ability_type' => 'missile',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_break($objects, 'pierced', 'excited');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_break($objects);

    }
  );
?>