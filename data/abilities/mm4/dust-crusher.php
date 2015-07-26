<?
// DUST CRUSHER
$ability = array(
  'ability_name' => 'Dust Crusher',
  'ability_token' => 'dust-crusher',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/030',
  'ability_master' => 'dust-man',
  'ability_number' => 'DCN-030',
  'ability_description' => 'The user collects a piece of stage debris with its powerful vaccuum and then blasts it at the target for massive damage!',
  'ability_type' => 'wind',
  'ability_type2' => 'impact',
  'ability_energy' => 8,
  'ability_damage' => 28,
  'ability_accuracy' => 94,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(1, -90, 25, 10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().' to collect debris!')
      ));
    $this_robot->robot_frame_styles = 'transform: scaleX(-1); -moz-transform: scaleX(-1); -webkit-transform: scaleX(-1); ';
    $this_robot->update_session();
    $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));
    $this_robot->robot_frame_styles = '';
    $this_robot->update_session();

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'success' => array(1, 140, 20, 10, $this_robot->print_robot_name().' blasts the '.$this_ability->print_ability_name().' debris at the target!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -55, 15, 10, 'The '.$this_ability->print_ability_name().' hit the target!'),
      'failure' => array(1, -75, 10, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(10, 0, 0),
      'success' => array(1, -35, 15, 10, 'The '.$this_ability->print_ability_name().' invigorated by the target!'),
      'failure' => array(1, -75, 10, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Return true on success
    return true;

  },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){
      $this_ability->ability_target = 'select_target';
    } else {
      $this_ability->ability_target = $this_ability->ability_base_target;
    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>