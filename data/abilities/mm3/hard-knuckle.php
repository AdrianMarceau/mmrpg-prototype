<?
// HARK KNUCKLE
$ability = array(
  'ability_name' => 'Hard Knuckle',
  'ability_token' => 'hard-knuckle',
  'ability_game' => 'MM03',
  'ability_group' => 'MM03/Weapons/020',
  'ability_master' => 'hard-man',
  'ability_number' => 'DWN-020',
  'ability_description' => 'The user fires a slow but powerful fist at the target that deals massive damage when it connects and lowers defense by {DAMAGE2}% without fail!',
  'ability_type' => 'impact',
  'ability_energy' => 4,
  'ability_speed' => -2,
  'ability_damage' => 20,
  'ability_damage2' => 10,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 90,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
      'class' => 'ability',
      'ability_token' => $this_ability->ability_token,
      'ability_frame' => 0,
      'ability_frame_offset' => array('x' => 120, 'y' => 0, 'z' => 10)
      );

    // Attach this ability attachment to the robot using it
    $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    $this_robot->update_session();

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => ($this_robot->robot_token == 'hard-man' ? 'throw' : 'shoot'),
      'success' => array(2, 60, ($this_robot->robot_token == 'hard-man' ? 10 : 0), -10, $this_robot->print_robot_name().' fires the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Attach this ability attachment to the robot using it
    unset($this_robot->robot_attachments[$this_attachment_token]);
    $this_robot->update_session();

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(60, 0, 0),
      'success' => array(0, 50, 0, 10, 'The '.$this_ability->print_ability_name().' crashes into the target!'),
      'failure' => array(0, -120, 0, -10, 'The '.$this_ability->print_ability_name().' flew past the target&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(60, 0, 0),
      'success' => array(0, 50, 0, 10, 'The '.$this_ability->print_ability_name().' crashes into the target!'),
      'failure' => array(0, -120, 0, -10, 'The '.$this_ability->print_ability_name().' flew past the target&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

    // Trigger a defense break if the ability was successful
    if ($target_robot->robot_status != 'disabled'
      && $target_robot->robot_defense > 0
      && $this_ability->ability_results['this_result'] != 'failure' && $this_ability->ability_results['this_amount'] > 0){
      // Decrease the target robot's defense stat
      $this_ability->damage_options_update(array(
        'kind' => 'defense',
        'frame' => 'defend',
        'percent' => true,
        'kickback' => array(10, 0, 0),
        'success' => array(1, 0, -6, -10, $target_robot->print_robot_name().'&#39;s shields were damaged!'),
        'failure' => array(1, 0, -6, -10, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'defense',
        'frame' => 'taunt',
        'percent' => true,
        'kickback' => array(0, 0, 0),
        'success' => array(1, 0, -6, -10, $target_robot->print_robot_name().'&#39;s shields were tempered!'),
        'failure' => array(1, 0, -6, -9999, '')
        ));
      $defense_damage_amount = ceil($target_robot->robot_defense * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount);
    }

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