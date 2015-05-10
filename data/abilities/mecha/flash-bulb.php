<?
// FLASH BULB
$ability = array(
  'ability_name' => 'Flash Bulb',
  'ability_token' => 'flash-bulb',
  'ability_game' => 'MM04',
  'ability_class' => 'mecha',
  'ability_description' => 'The user generates an intense burst of light that damages the target and lowers its attack by {DAMAGE2}%.',
  'ability_energy' => 0,
  'ability_type' => 'electric',
  'ability_damage' => 10,
  'ability_damage2' => 2,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 0, 'impact' => 0);
    //if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 4, 'impact' => 5); }
    //elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 0, 'impact' => 1); }
    
    // Change the image to the full-screen rain effect
    $this_ability->ability_frame_classes = 'sprite_fullscreen ';
    $this_ability->ability_frame_styles = 'opacity: 0.5; filter: alpha(opacity=50); ';
    $this_ability->update_session();
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array($this_frames['target'], -5, 0, -10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Ensure this robot stays in the summon position for the duration of the attack
    $this_robot->robot_frame = 'defend';
    $this_robot->update_session();
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], -5, 0, 99, 'The '.$this_ability->print_ability_name().' blinded the target!'),
      'failure' => array($this_frames['impact'], -5, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], -5, 0, 99, 'The '.$this_ability->print_ability_name().' enlightened the target!'),
      'failure' => array($this_frames['impact'], -5, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    
    // Change the image to the full-screen rain effect
    $this_ability->ability_frame_classes = '';
    $this_ability->ability_frame_styles = 'display: none; ';
    $this_ability->update_session();
    
      // Randomly trigger a speed break if the ability was successful
    if ($target_robot->robot_status != 'disabled'
      && $target_robot->robot_attack > 0
      && $this_ability->ability_results['this_result'] != 'failure'
      && $this_ability->ability_results['this_amount'] > 0){
      // Decrease the target robot's attack stat
      $this_ability->damage_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'frame' => 'defend',
        'kickback' => array(10, 0, 0),
        'success' => array(9, 0, -6, 10, $target_robot->print_robot_name().'&#39;s weapons were damaged!'),
        'failure' => array(9, 0, -6, -10, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'attack',
        'percent' => true,
        'frame' => 'taunt',
        'kickback' => array(5, 0, 0),
        'success' => array(9, 0, -6, 10, $target_robot->print_robot_name().'&#39;s weapons improved!'),
        'failure' => array(9, 0, -6, -9999, '')
        ));
      $attack_damage_amount = ceil($target_robot->robot_attack * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $attack_damage_amount);
    }
    
    // Change the image to the full-screen rain effect
    $this_ability->ability_frame_classes = '';
    $this_ability->ability_frame_styles = '';
    $this_ability->update_session();
    
    // Ensure this robot stays goes back to the base frame after the attack
    $this_robot->robot_frame = 'base';
    $this_robot->update_session();
    
    // Return true on success
    return true;
      
    }
  );
?>