<?
// ATTACK BLAZE
$ability = array(
  'ability_name' => 'Attack Blaze',
  'ability_token' => 'attack-blaze',
  'ability_game' => 'MMRPG',
  'ability_description' => 'The user ignites its own weapons with a powerful blaze, raising attack by {RECOVERY}%!',
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
    
    // Decrease the target robot's attack stat
    $this_ability->recovery_options_update(array(
      'kind' => 'attack',
      'frame' => 'taunt',
      'percent' => true,
      'kickback' => array(10, 0, 0),
      'success' => array(3, 0, 5, -10, $this_robot->print_robot_name().'&#39;s weapons were ignited!'),
      'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_robot_name().'&hellip;')
      ), true);
    $this_ability->damage_options_update(array(
      'kind' => 'attack',
      'frame' => 'damage',
      'percent' => true,
      'kickback' => array(0, 0, 0),
      'success' => array(3, 0, 5, -10, $this_robot->print_robot_name().'&#39;s weapons were burned!'),
      'failure' => array(0, 0, -9999, -9999, 'It had no effect on '.$this_robot->print_robot_name().'&hellip;')
      ), true);
    $attack_recovery_amount = $this_ability->ability_recovery;
    //if (!empty($this_robot->robot_core) && $this_robot->robot_core == $this_ability->ability_type){ $attack_recovery_amount = $attack_recovery_amount * MMRPG_SETTINGS_COREBOOST_MULTIPLIER; }
    $attack_recovery_amount = ceil($this_robot->robot_attack * ($attack_recovery_amount / 100));
    $this_robot->trigger_recovery($this_robot, $this_ability, $attack_recovery_amount);
    
    // Return true on success
    return true;
      
    }
  );
?>