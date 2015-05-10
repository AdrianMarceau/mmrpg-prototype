<?
// MAGFLY MISSILE
$ability = array(
  'ability_name' => 'Magfly Missile',
  'ability_token' => 'magfly-missile',
  'ability_game' => 'MM03',
  'ability_class' => 'mecha',
  'ability_description' => 'The user generates a strong magnetic force that pulls it toward a target to drain {DAMAGE}% of their weapon energy!',
  'ability_type' => 'missile',
  'ability_energy' => 0,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_accuracy' => 90,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define the target and impact frames based on user
    $this_frames = array('target' => 0, 'impact' => 1);
    if (preg_match('/-2$/', $this_robot->robot_token)){ $this_frames = array('target' => 2, 'impact' => 3); }
    elseif (preg_match('/-3$/', $this_robot->robot_token)){ $this_frames = array('target' => 4, 'impact' => 5); }
    
    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'kickback' => array(50, 0, 0),
      'success' => array($this_frames['target'], 50, -15, 10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->robot_frame_styles = 'display: none; ';
    $this_robot->update_session();
    $this_robot->trigger_target($target_robot, $this_ability);
    
    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'weapons',
      'percent' => true,
      'kickback' => array(40, 0, 0),
      'success' => array($this_frames['impact'], 40, -15, 10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
      'failure' => array($this_frames['impact'], -120, -15, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'weapons',
      'percent' => true,
      'kickback' => array(20, 0, 0),
      'success' => array($this_frames['impact'], 40, -15, 10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
      'failure' => array($this_frames['impact'], -120, -15, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $weapons_damage_amount = ceil($target_robot->robot_base_weapons * ($this_ability->ability_damage / 100));
    $target_robot->trigger_damage($this_robot, $this_ability, $weapons_damage_amount);
    $this_robot->robot_frame_styles = '';
    $this_robot->update_session();
    
    // Return true on success
    return true;
      
    }
  );
?>