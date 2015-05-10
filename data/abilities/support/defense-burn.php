<?
// DEFENSE BURN
$ability = array(
  'ability_name' => 'Defense Burn',
  'ability_token' => 'defense-burn',
  'ability_game' => 'MMRPG',
  'ability_description' => 'The user breaks down the target&#39;s shields using fire, lowering its defense by {DAMAGE}%!',
  'ability_energy' => 4,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_type' => 'flame',
  'ability_accuracy' => 95,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 85, 0, 10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Decrease the target robot's defense stat
    $this_ability->damage_options_update(array(
      'kind' => 'defense',
      'frame' => 'damage',
      'percent' => true,
      'kickback' => array(10, 0, 0),
      'success' => array(1, -50, 0, 10, $target_robot->print_robot_name().'&#39;s shields were burned!'),
      'failure' => array(1, -75, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'defense',
      'frame' => 'taunt',
      'percent' => true,
      'kickback' => array(0, 0, 0),
      'success' => array(1, -50, 0, 10, $target_robot->print_robot_name().'&#39;s shields were ignited!'),
      'failure' => array(1, -75, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
      ));
    $defense_damage_amount = ceil($target_robot->robot_defense * ($this_ability->ability_damage / 100));
    $trigger_options = array('apply_stat_modifiers' => false);
    $target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount, true, $trigger_options);
    
    // Return true on success
    return true;
      
    }
  );
?>