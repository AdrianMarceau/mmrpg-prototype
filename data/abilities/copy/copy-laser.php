<?
// COPY LASER
$ability = array(
  'ability_name' => 'Copy Laser',
  'ability_token' => 'copy-laser',
  'ability_group' => 'MMRPG/Weapons/Copy',
  'ability_type' => 'copy',
  'ability_description' => 'When used by a Copy core robot, this move fires an elemental laser based on the target\'s core type.  When used by any other robot, this move fires a laser based on the user\'s own core type.  The afterglow left by this ability amplifies matching elemental damage by {RECOVERY2}% and can be aimed at any robot on the target\'s side of the field.',
  'ability_energy' => 8,
  'ability_damage' => 40,
  'ability_recovery2' => 30,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>