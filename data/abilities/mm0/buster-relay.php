<?
// BUSTER RELAY
$ability = array(
  'ability_name' => 'Buster Relay',
  'ability_token' => 'buster-relay',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Weapons/T0',
  'ability_image_sheets' => 0,
  'ability_description' => 'The user relays a buster charge they are currently holding to another robot on their side of the field, transferring any stat changes or elemental boosts to the new robot. Charges transferred this way intensify in power with each passing and will automatically merge with others of the same kind and element.',
  'ability_energy' => 0,
  'ability_accuracy' => 100,
  'ability_target' => 'select_this',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 0, 0, 10, $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Define the default ability info vars
    $relay_buster_token = '';
    $relay_buster_info = array();
    $relay_buster_object = false;

    // Automatically fail if the user has targetted itself
    if ($target_robot->robot_id == $this_robot->robot_id){

      // Target this robot's self and show the ability failing
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(9, 0, 0, -10,
          'But this robot cannot target itself&hellip;<br />'
          )
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

    }
    // Automatically fail if there was no buster charge to transfer
    elseif (empty($this_robot->robot_attachments)){

      // Target this robot's self and show the ability failing
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(9, 0, 0, -10,
          'But there was nothing to transfer&hellip;<br />'
          )
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

    }
    // Otherwise if not empty, loop through this robot's attachments, looking for a buster
    elseif (!empty($this_robot->robot_attachments)){
      $attachment_key = 0;
      foreach ($this_robot->robot_attachments AS $attachment_token => $attachment_info){
        // If this is a buster charge of any kind, move it to the target robot
        if (preg_match('/^([-_a-z0-9]+)-buster$/i', $attachment_token)){

          // Collect the information for this buster ability
          $relay_buster_token = $attachment_token;
          $relay_buster_info = $attachment_info;
          $relay_buster_object = new mmrpg_ability($this_battle, $this_player, $this_robot, $relay_buster_info);

          // Remove this attachment from the source robot
          unset($this_robot->robot_attachments[$relay_buster_token]);
          $this_robot->update_session();

          // If this buster has any boosters or breakers, intensify their values
          foreach ($relay_buster_info AS $field => $value){
            if (preg_match('/^attachment_(damage|recovery)(_input|_output)?(_booster|_breaker)(_[a-z]+)?$/i', $field)){
              $new_value = $value;
              if (!isset($relay_buster_info[$field.'_base'])){ $relay_buster_info[$field.'_base'] = $value; }
              $base = $relay_buster_info[$field.'_base'];
              $new_value = $new_value * $base;
              if (isset($target_robot->robot_attachments[$relay_buster_token][$field])){
                $merge = $target_robot->robot_attachments[$relay_buster_token][$field];
                $new_value = $new_value * $merge;
              }
              $relay_buster_info[$field] = $new_value;
            }
          }

          // Append this attachment to the new target robot
          $temp_buster_exists = isset($target_robot->robot_attachments[$relay_buster_token]) ? true : false;
          $target_robot->robot_frame = $attachment_key % 2 == 0 ? 'defend' : 'taunt';
          $target_robot->robot_attachments[$relay_buster_token] = $relay_buster_info;
          $target_robot->update_session();

          // Trigger the robot target and show the buster charge moving
          $this_ability->target_options_update(array(
            'frame' => $attachment_key % 2 == 0 ? 'taunt' : 'summon',
            'success' => array(9, 0, 0, -10,
              'The '.$relay_buster_object->print_ability_name().' charge was '.
              (!$temp_buster_exists ? 'transferred to '.$target_robot->print_robot_name().'!' : 'merged with '.$target_robot->print_robot_name().'&#39;s!').
              '<br />'
              )
            ));
          $this_robot->trigger_target($target_robot, $this_ability);

          // Reset the target robot's frame
          $target_robot->robot_frame = 'base';
          $target_robot->update_session();

          // Increment the attachment key
          $attachment_key++;

        }
      }
      // Automatically fail if there was no buster charge to transfer
      if (empty($relay_buster_token)){

        // Target this robot's self and show the ability failing
        $this_ability->target_options_update(array(
          'frame' => 'summon',
          'success' => array(9, 0, 0, -10,
            'But there weren\'t any buster charges to transfer&hellip;<br />'
            )
          ));
        $this_robot->trigger_target($this_robot, $this_ability);

      }
    }

    // Return true on success
    return true;

    }
  );
?>