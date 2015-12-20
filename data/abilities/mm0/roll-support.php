<?
// ROLL SUPPORT
$ability = array(
  'ability_name' => 'Roll Support',
  'ability_token' => 'roll-support',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Roll',
  'ability_description' => 'The user offers support to its own team by recovering energy, attack, defense, and speed stats by {RECOVERY}% for all robots on the user\'s side of the field!',
  'ability_energy' => 16,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>