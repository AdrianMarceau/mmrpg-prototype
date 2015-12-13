<?
// COPY SOUL
$ability = array(
  'ability_name' => 'Copy Soul',
  'ability_token' => 'copy-soul',
  'ability_group' => 'MMRPG/Weapons/Copy',
  'ability_type' => 'copy',
  'ability_description' => 'When used by a Copy core robot, this move replaces the user\'s core type with that of the target for the rest of the battle.  When used by any other kind of robot, this move takes on the core type of the user to deal elemental summon damage to the target.',
  'ability_energy' => 4,
  'ability_damage' => 18,
  'ability_accuracy' => 94,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>