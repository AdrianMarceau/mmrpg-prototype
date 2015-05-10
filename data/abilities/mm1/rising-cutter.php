<?
// RISING CUTTER
$ability = array(
  'ability_name' => 'Rising Cutter',
  'ability_token' => 'rising-cutter',
  'ability_game' => 'MM01',
  'ability_description' => 'The user summons a giant cutter below the target to inflict massive damage and occasionally lower defense by {DAMAGE2}%!',
  'ability_type' => 'cutter',
  'ability_energy' => 8,
  'ability_damage' => 20,
  'ability_damage2' => 20,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(0, 430, 0, 10, $this_robot->print_robot_name().' summons the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(0, 15, 0),
      'success' => array(1, 0, 30, 10, 'The '.$this_ability->print_ability_name().' sliced through the target!'),
      'failure' => array(1, 0, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, 0, 30, 10, 'The '.$this_ability->print_ability_name().' sliced through the target!'),
      'failure' => array(1, 0, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $this_robot->robot_frame = 'summon';
    $this_robot->update_session();
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    $this_robot->robot_frame = 'base';
    $this_robot->update_session();
    
    // Return true on success
    return true;
      
  }
  );
?>