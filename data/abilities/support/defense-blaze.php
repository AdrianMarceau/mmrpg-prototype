<?
// DEFENSE BLAZE
$ability = array(
  'ability_name' => 'Defense Blaze',
  'ability_token' => 'defense-blaze',
  'ability_game' => 'MMRPG',
  'ability_description' => 'The user ignites its own shields with a powerful blaze, raising defense by {RECOVERY}%!',
  'ability_energy' => 4,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_type' => 'flame',
  'ability_accuracy' => 100,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(2, 0, -20, -10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);
    
    // Decrease the target robot's defense stat
    $this_ability->recovery_options_update(array(
      'kind' => 'defense',
      'frame' => 'taunt',
      'percent' => true,
      'kickback' => array(10, 0, 0),
      'success' => array(3, 0, 5, -10, $this_robot->print_robot_name().'&#39;s shields were ignited!'),
      'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_robot_name().'&hellip;')
      ), true);
    $this_ability->damage_options_update(array(
      'kind' => 'defense',
      'frame' => 'damage',
      'percent' => true,
      'kickback' => array(0, 0, 0),
      'success' => array(3, 0, 5, -10, $this_robot->print_robot_name().'&#39;s shields were burned!'),
      'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_robot_name().'&hellip;')
      ), true);
    $defense_recovery_amount = $this_ability->ability_recovery;
    //if (!empty($this_robot->robot_core) && $this_robot->robot_core == $this_ability->ability_type){ $defense_recovery_amount = $defense_recovery_amount * MMRPG_SETTINGS_COREBOOST_MULTIPLIER; }
    $defense_recovery_amount = ceil($this_robot->robot_defense * ($defense_recovery_amount / 100));
    $this_robot->trigger_recovery($this_robot, $this_ability, $defense_recovery_amount);
    
    // Return true on success
    return true;
      
    }
  );
?>