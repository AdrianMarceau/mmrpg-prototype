<?
// FIRE CHASER
$ability = array(
  'ability_name' => 'Fire Chaser',
  'ability_token' => 'fire-chaser',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/007',
  'ability_master' => 'fire-man',
  'ability_number' => 'DLN-007',
  'ability_description' => 'The user a unleashes a powerful wave of fire that chases the target to deal massive damage.  The slower the user campared to the target, the greater this ability\'s power.',
  'ability_type' => 'flame',
  'ability_type2' => 'swift',
  'ability_energy' => 8,
  'ability_damage' => 24,
  'ability_accuracy' => 94,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array(0, 100, 0, 10, $this_robot->print_robot_name().' unleashes a '.$this_ability->print_ability_name().'!'),
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(15, 0, 0),
      'success' => array(1, -75, 0, 10, 'The '.$this_ability->print_ability_name().' chased the target!'),
      'failure' => array(1, -100, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, -75, 0, 10, 'The '.$this_ability->print_ability_name().' ignited the target!'),
      'failure' => array(1, -100, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    if ($target_robot->robot_speed > $this_robot->robot_speed){ $speed_multiplier = $target_robot->robot_speed / $this_robot->robot_speed; }
    elseif ($this_robot->robot_speed > $target_robot->robot_speed){ $speed_multiplier = $this_robot->robot_speed / $target_robot->robot_speed; }
    else { $speed_multiplier = 1; }
    $energy_damage_amount = ceil($this_ability->ability_damage * $speed_multiplier);
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