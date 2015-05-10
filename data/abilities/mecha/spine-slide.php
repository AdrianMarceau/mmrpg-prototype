<?
// SPINE SLIDE
$ability = array(
  'ability_name' => 'Spine Slide',
  'ability_token' => 'spine-slide',
  'ability_game' => 'MM01',
  'ability_class' => 'mecha',
  'ability_description' => 'The user slides across the ground until it crashes into the target to inflict damage.',
  'ability_type' => 'electric',
  'ability_energy' => 0,
  'ability_damage' => 12,
  'ability_accuracy' => 100,
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
      'kickback' => array(40, 0, 0),
      'success' => array($this_frames['target'], 40, 0, 10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->robot_frame_styles = 'display: none; ';
    $this_robot->update_session();
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(30, 0, 0),
      'success' => array($this_frames['impact'], 10, 0, 10, 'The '.$this_ability->print_ability_name().' zapped through the target!'),
      'failure' => array($this_frames['impact'], -30, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($this_frames['impact'], 10, 0, 10, 'The '.$this_ability->print_ability_name().' zapped through the target!'),
      'failure' => array($this_frames['impact'], -30, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
    $this_robot->robot_frame_styles = '';
    $this_robot->update_session();
    if ($target_robot->robot_energy < 1){ $target_robot->trigger_disabled($this_robot, $this_ability); }

    // Return true on success
    return true;

    }
  );
?>