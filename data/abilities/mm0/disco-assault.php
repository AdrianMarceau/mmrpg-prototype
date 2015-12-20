<?
// DISCO ASSAULT
$ability = array(
  'ability_name' => 'Disco Assault',
  'ability_token' => 'disco-assault',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Disco',
  'ability_description' => 'The user assaults the opposing team by damaging energy, attack, defense, and speed stats by {DAMAGE}% for all robots on the target\'s side of the field!',
  'ability_energy' => 16,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>