<?
// DEFENSE BURN
$ability = array(
  'ability_name' => 'Defense Burn',
  'ability_token' => 'defense-burn',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/05/FlameDefense',
  'ability_description' => 'The user breaks down the target\'s shield systems using an anti-defensive flare program, lowering its defense by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'flame',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_break($objects, 'burned', 'ignited');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_break($objects);

    }
  );
?>