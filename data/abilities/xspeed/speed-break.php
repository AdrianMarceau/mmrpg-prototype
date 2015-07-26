<?
// SPEED BREAK
$ability = array(
  'ability_name' => 'Speed Break',
  'ability_token' => 'speed-break',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/Speed',
  'ability_description' => 'The user breaks down the target&#39;s mobility, lowering its speed by {DAMAGE}%!',
  'ability_energy' => 8,
  'ability_damage' => 30,
  'ability_damage_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, -2, 0, -10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Decrease the target robot's speed stat
    $this_ability->damage_options_update(array(
      'kind' => 'speed',
      'percent' => true,
      'kickback' => array(10, 0, 0),
      'success' => array(0, -2, 0, -10, $target_robot->print_robot_name().'&#39;s mobility was damaged!'),
      'failure' => array(9, -2, 0, -10, 'It had no effect on '.$target_robot->print_robot_name().'&hellip;')
      ));
    $speed_damage_amount = ceil($target_robot->robot_speed * ($this_ability->ability_damage / 100));
    $target_robot->trigger_damage($this_robot, $this_ability, $speed_damage_amount);

    // Return true on success
    return true;

    },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define the allow targeting flag for the ability
    $temp_allow_targeting = false;

    // If this ability is being used by a special support robot, allow targetting
    $temp_support_robots = array('roll', 'disco', 'rhythm');
    if (in_array($this_robot->robot_token, $temp_support_robots) && $target_player->counters['robots_active'] > 1){ $temp_allow_targeting = true; }

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){ $temp_allow_targeting = true; }

    // If this ability targeting is allowed
    if ($temp_allow_targeting){
      // Update this ability's targetting setting
      $this_ability->ability_target = 'select_target';
    }
    // Else if the ability attachment is not there, change the target back to auto
    else {
      // Update this ability's targetting setting
      $this_ability->ability_target = 'auto';
    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>