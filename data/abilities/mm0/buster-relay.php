<?
// BUSTER RELAY
$ability = array(
  'ability_name' => 'Buster Relay',
  'ability_token' => 'buster-relay',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Weapons/T0',
  'ability_image_sheets' => 0,
  'ability_description' => 'The user relays a buster charge they are currently holding to another robot on their team, transferring any stat changes or elemental boosts to the new robot.',
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

    // Loop through this robot's attachments, looking for a buster
    $relay_buster_token = '';
    $relay_buster_object = false;
    $relay_buster_info = array();
    if (!empty($this_robot->robot_attachments)){
      foreach ($this_robot->robot_attachments AS $attachment_token => $attachment_info){
        // If this is a buster charge of any kind, save its info and break
        if (preg_match('/^([-_a-z0-9]+)-buster$/i', $attachment_token)){
          $relay_buster_token = $attachment_token;
          $relay_buster_info = $attachment_info;
          $relay_buster_object = new mmrpg_ability($this_battle, $this_player, $this_robot, $relay_buster_info);
          break;
        }
      }
    }

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
    // Automatically fila if there was no buster charge to transfer
    elseif (empty($relay_buster_token)){

      // Target this robot's self and show the ability failing
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(9, 0, 0, -10,
          'But there was nothing to transfer&hellip;<br />'
          )
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

    }
    // Ensure there was a buster charge to pass on to the target
    else {

      // Remove this attachment from the source robot
      unset($this_robot->robot_attachments[$relay_buster_token]);
      $this_robot->update_session();

      // Append this attachment to the new target robot
      $target_robot->robot_frame = 'taunt';
      $target_robot->robot_attachments[$relay_buster_token] = $relay_buster_info;
      $target_robot->update_session();

      // Trigger the robot target and show the buster charge moving
      $this_ability->target_options_update(array(
        'frame' => 'defend',
        'success' => array(9, 0, 0, -10,
          'The '.$relay_buster_object->print_ability_name().' charge was transferred to '.$target_robot->print_robot_name().'!<br />'
          )
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

      // Reset the target robot's frame
      $target_robot->robot_frame = 'base';
      $target_robot->update_session();

    }

    // Return true on success
    return true;

    }
  );
?>