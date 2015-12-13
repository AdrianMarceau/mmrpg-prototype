<?
// COPY SHIELD
$ability = array(
  'ability_name' => 'Copy Shield',
  'ability_token' => 'copy-shield',
  'ability_group' => 'MMRPG/Weapons/Copy',
  'ability_type' => 'copy',
  'ability_description' => 'When used by a Copy core robot, this move raises an elemental shield based on the target\'s core type.  When used by any other robot, this move raises a shield based on the user\'s own core type.  The barrier created by this ability resists matching elemental damage by {RECOVERY2}% and can be equipped to any robot on the user\'s side of the field.',
  'ability_energy' => 8,
  'ability_damage' => 30,
  'ability_recovery2' => 40,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>