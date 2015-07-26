<?
// COPY SHOT
$ability = array(
  'ability_name' => 'Copy Shot',
  'ability_token' => 'copy-shot',
  'ability_group' => 'MMRPG/Weapons/Copy',
  'ability_type' => 'copy',
  'ability_description' => 'When used by a Copy core robot, this move replaces itself with the target\'s last used ability for the rest of the battle.  When used by any other kind of robot, this move takes on the core type of the user to deal elemental shot damage to the target.',
  'ability_energy' => 4,
  'ability_damage' => 14,
  'ability_accuracy' => 98,
  'ability_function' => function($objects){

    // Pull in the global index
    global $mmrpg_index;
    // Extract all objects into the current scope
    extract($objects);

    // Define the frames based on current character
    $temp_ability_frames = array('target' => 0, 'damage' => 1, 'summon' => 2);
    if ($this_robot->robot_token == 'mega-man'){ $temp_ability_frames = array('target' => 0, 'damage' => 1, 'summon' => 2); }
    elseif ($this_robot->robot_token == 'bass'){ $temp_ability_frames = array('target' => 3, 'damage' => 4, 'summon' => 5); }
    elseif ($this_robot->robot_token == 'proto-man'){ $temp_ability_frames = array('target' => 6, 'damage' => 7, 'summon' => 8); }

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
      'class' => 'ability',
      'sticky' => true,
      'ability_token' => $this_ability->ability_token,
      'ability_frame' => $temp_ability_frames['summon'],
      'ability_frame_animate' => array($temp_ability_frames['summon']),
      'ability_frame_offset' => array('x' => -10, 'y' => 20, 'z' => -10)
      );

    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'shoot',
      'success' => array($temp_ability_frames['target'], 105, 0, 10, $this_robot->print_robot_name().' fires a '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(10, 0, 0),
      'success' => array($temp_ability_frames['damage'], -70, 0, 10, 'The '.$this_ability->print_ability_name().' hit the target!'),
      'failure' => array($temp_ability_frames['damage'], -70, 0, -10, 'The '.$this_ability->print_ability_name().' missed&hellip;')
      ));
    $target_robot->trigger_damage($this_robot, $this_ability, $this_ability->ability_damage);

    // Check to ensure the ability was a success before continuing
    if ($this_ability->ability_results['this_result'] != 'failure'){

      // Ensure the target robot has an ability history to draw from
      if (!empty($target_robot->history['triggered_abilities'])){

        // Find the position of the current copy-shot ability
        $this_ability_key = array_search($this_ability->ability_token, $this_robot->robot_abilities);

        // Loop through the opponent's ability history in reverse
        $num_triggered_abilities = count($target_robot->history['triggered_abilities']);
        $new_ability_token = $target_robot->history['triggered_abilities'][$num_triggered_abilities - 1];
        $new_ability_info = mmrpg_ability::get_index_info($new_ability_token);

        // Skip these abilities as they cannot be copied


        // If the current robot does not already have this ability
        if (!empty($new_ability_info)
          && !in_array($new_ability_token, $this_robot->robot_abilities)
          && (empty($new_ability_info['ability_class']) || $new_ability_info['ability_class'] == 'master')){

          // Attach the ability to this robot
          $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
          $this_robot->update_session();

          // Copy the current ability to this robot's list, and update
          $this_robot->robot_frame = 'taunt';
          $this_robot->robot_abilities[$this_ability_key] = $new_ability_token;
          $this_robot->update_session();
          $this_player->player_frame = 'victory';
          $this_player->update_session();
          // Create the ability object to trigger data loading
          $this_new_ability = new mmrpg_ability($this_battle, $this_player, $this_robot, $new_ability_info);
          $this_new_ability->update_session();
          // Create an event displaying the new copied ability
          //$event_header = $this_robot->robot_name.'&#39;s '.$this_ability->ability_name;
          $event_header = $this_new_ability->ability_name.' Unlocked';
          $event_body = $this_ability->print_ability_name().' downloads the target&#39;s battle data&hellip;<br />';
          //$event_body .= $this_robot->print_robot_name().' learned how to use '.$this_new_ability->print_ability_name().'!';
          $event_body .= $this_new_ability->print_ability_name().' can now be used in battle!';
          $event_options = array();
          $event_options['console_show_target'] = false;
          $event_options['this_ability'] = $this_new_ability;
          $event_options['this_ability_image'] = 'icon';
          $event_options['console_show_this_robot'] = false;
          $event_options['canvas_show_this_ability'] = false;
          $event_options['console_show_this_ability'] = true;
          $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

          // Attach the ability to this robot
          unset($this_robot->robot_attachments[$this_attachment_token]);
          $this_robot->update_session();

          // Check to see if this robot is being used by a human player
          if ($this_player->player_side == 'left'){

            //$this_battle->events_create(false, false, 'debug', 'player side left!', $event_options);

            // Remove the copy shot from this robot's battle settings and replace with new ability
            $temp_ability_settings = $_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_abilities'];
            $temp_new_ability_settings = array();
            if (!isset($temp_ability_settings[$this_new_ability->ability_token])){
              foreach ($temp_ability_settings AS $array){ $temp_new_ability_settings[] = $array['ability_token']; }
              $temp_overwrite_position = array_search($this_ability->ability_token, $temp_new_ability_settings);
              $temp_new_ability_settings[$temp_overwrite_position] = $this_new_ability->ability_token;
              $temp_ability_settings = array();
              foreach ($temp_new_ability_settings AS $token){ $temp_ability_settings[$token] = array('ability_token' => $token); }
              $_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_abilities'] = $temp_ability_settings;
              // Unset this ability in the robot's settings
              //unset($_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_abilities'][$this_ability->ability_token]);
            }

            // Unlock this ability for the player permanently
            $show_event = !mmrpg_prototype_ability_unlocked('', '', $this_new_ability->ability_token) ? true : false;
            $temp_player_info = array('player_token' => $this_player->player_token);
            $temp_robot_info = array('robot_token' => $this_robot->robot_token);
            $temp_ability_info = array('ability_token' => $this_new_ability->ability_token);
            //mmrpg_game_unlock_ability($temp_player_info, $temp_robot_info, $temp_ability_info);
            mmrpg_game_unlock_ability($temp_player_info, false, $temp_ability_info, $show_event);

          }

        }

      }

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