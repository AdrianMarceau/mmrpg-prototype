<?
// DEFENSE COOL
$ability = array(
  'ability_name' => 'Defense Cool',
  'ability_token' => 'defense-cool',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/03/FreezeDefense',
  'ability_description' => 'The user powers up its own shield systems with a defensive blizzard program, raising defense by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'freeze',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Call the common elemental stat boost function from here
    return mmrpg_ability::ability_function_elemental_defense_boost($objects, 'cooled', 'chilled');

    },
  'ability_function_onload' => function($objects){

    // Call the common elemental stat boost onload function from here
    return mmrpg_ability::ability_function_onload_elemental_defense_boost($objects);

    }
  );
?>