<?
// DEFENSE SWAP
$ability = array(
  'ability_name' => 'Defense Swap',
  'ability_token' => 'defense-swap',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/Defense',
  'ability_description' => 'The user triggers a glitch in the prototype that swaps the user\'s own defense stats with the target\'s!',
  'ability_energy' => 8,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
      'class' => 'ability',
      'ability_token' => $this_ability->ability_token,
      'ability_frame' => 0,
      'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
      );

    // Attach this ability to the target temporarily
    $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    $target_robot->update_session();

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 0, 10, -10, $this_robot->print_robot_name().' triggered a '.$this_ability->print_ability_name().' with '.$target_robot->print_robot_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Remove this ability from the target
    unset($target_robot->robot_attachments[$this_attachment_token]);
    $target_robot->update_session();

    // Create a function that increases or decreases a robot's defense to target
    $temp_defense_function = function($this_robot, $this_ability, $temp_this_defense, $temp_target_defense){
      global $this_battle;

      // Collect the target's current defense amount
      //$temp_this_defense = $this_robot->robot_defense.'/'.$this_robot->robot_base_defense;

      //$this_battle->events_create(false, false, 'DEBUG '.__LINE__, '$temp_this_defense = '.$temp_this_defense.', $temp_target_defense = '.$temp_target_defense);

      // Only continue if this robot and the target's defense are not equal
      if ($temp_this_defense != $temp_target_defense){

        // Break apart the defense into its current and base amounts
        list($temp_defense, $temp_base_defense) = explode('/', $temp_target_defense);

        // Update this robot's values with the random data
        $this_robot->robot_defense = $temp_defense;
        $this_robot->robot_base_defense = $temp_base_defense;
        $this_robot->update_session();

        // Target this robot's self
        $is_her = in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm', 'splash-woman')) ? true : false;
        $is_mecha = $this_robot->robot_class == 'mecha' ? true : false;
        $this_ability->target_options_update(array(
          'frame' => 'defend',
          'success' => array(9, 0, 10, -10, $this_robot->print_robot_name().'&#39;s defense stats were modified&hellip;<br /> '.($is_her ? 'Her' : ($is_mecha ? 'Its' : 'His')).' new defense stats are '.$this_robot->print_robot_defense().' / '.$this_robot->print_robot_base_defense().'!')
          ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

      }
      // Otherwise, if the two already have equal defense amounts
      else {

        // Target this robot's self and show the ability failing
        $this_ability->target_options_update(array(
          'frame' => 'defend',
          'success' => array(9, 0, 0, -10, $this_robot->print_robot_name().'&#39;s defense stats were not affected&hellip;')
          ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

        // Return true on success (well, failure, but whatever)
        return true;

      }

    };

    // Collect the target's current defense amount
    $temp_this_defense = $this_robot->robot_defense.'/'.$this_robot->robot_base_defense;
    // Collect this robot's current defense amount
    $temp_target_defense = $target_robot->robot_defense.'/'.$target_robot->robot_base_defense;

    // Update this robot's defense to that of the target's
    $temp_defense_function($this_robot, $this_ability, $temp_this_defense, $temp_target_defense);
    // Update the target's defense to that of this robot
    $temp_defense_function($target_robot, $this_ability, $temp_target_defense, $temp_this_defense);

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
