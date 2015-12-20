<?
// RHYTHM SHUFFLE
$ability = array(
  'ability_name' => 'Rhythm Shuffle',
  'ability_token' => 'rhythm-shuffle',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Rhythm',
  'ability_description' => 'The user triggers an exploit in the battle system that glitches out and shuffles the positions of all robots on both sides of the field!',
  'ability_energy' => 16,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>