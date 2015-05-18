<?
// ATTACK SHUFFLE
$ability = array(
  'ability_name' => 'Attack Shuffle',
  'ability_token' => 'attack-shuffle',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Support/Attack2',
  'ability_description' => 'The user triggers a dangerous glitch in the prototype that shuffles the life attack of all robots on the field!',
  'ability_energy' => 16,
  'ability_speed' => -1,
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

    /*
     * SHOW ABILITY TRIGGER
     */

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(9, 0, 10, -10, $this_robot->print_robot_name().' triggered an '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);


    /*
     * SWAP STATS FUNCTION
     */

    // Create a function that increases or decreases a robot's attack to target
    $temp_attack_function = function($this_robot, $this_ability, $temp_this_attack, $temp_target_attack){
      global $this_battle;

      // Only continue if this robot and the target's attack are not equal
      if ($temp_this_attack != $temp_target_attack){

        // Break apart the attack into its current and base amounts
        list($temp_attack, $temp_base_attack) = explode('/', $temp_target_attack);

        // Update this robot's values with the random data
        $this_robot->robot_attack = $temp_attack;
        $this_robot->robot_base_attack = $temp_base_attack;
        $this_robot->update_session();

        // Target this robot's self
        $is_her = in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm', 'splash-woman')) ? true : false;
        $is_mecha = $this_robot->robot_class == 'mecha' ? true : false;
        $this_ability->target_options_update(array(
          'frame' => 'defend',
          'success' => array(9, 0, 10, -10, $this_robot->print_robot_name().'&#39;s life attack was modified&hellip;<br /> '.($is_her ? 'Her' : ($is_mecha ? 'Its' : 'His')).' new attack stats are '.$this_robot->print_robot_attack().' / '.$this_robot->print_robot_base_attack().'!')
          ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

      }
      // Otherwise, if the two already have equal attack amounts
      else {

        // Target this robot's self and show the ability failing
        $this_ability->target_options_update(array(
          'frame' => 'defend',
          'success' => array(9, 0, 0, -10, $this_robot->print_robot_name().'&#39;s life attack was not affected&hellip;')
          ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

        // Return true on success (well, failure, but whatever)
        return true;

      }

    };

    // Collect attack amounts for all robots on the field into an array and shuffle
    $temp_this_attack_options = array();
    $temp_target_attack_options = array();
    foreach ($this_player->values['robots_active'] AS $info){ $temp_this_attack_options[] = $info['robot_attack'].'/'.$info['robot_base_attack']; }
    foreach ($target_player->values['robots_active'] AS $info){ $temp_target_attack_options[] = $info['robot_attack'].'/'.$info['robot_base_attack']; }

    // Loop through the battle positions and swap attack for opposing robots
    for ($i = 0; $i < 8; $i++){

      // If the attack for either side is not set, break
      if (!isset($temp_this_attack_options[$i])){ break; }
      elseif (!isset($temp_target_attack_options[$i])){ break; }
      // Else if somehow a robot doesn't in this key, break
      elseif (!isset($this_player->values['robots_active'][$i])){ break; }
      elseif (!isset($target_player->values['robots_active'][$i])){ break; }

      // Collect the current attack values for each side
      $temp_this_attack = $temp_this_attack_options[$i];
      $temp_target_attack = $temp_target_attack_options[$i];

      // If this is the first index, apply to the main this/target objects
      if ($i == 0){

        // Attach this ability attachment to this robot temporarily
        $this_robot->robot_frame = 'taunt';
        $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $this_robot->update_session();
        // Attach this ability attachment to this robot temporarily
        $target_robot->robot_frame = 'defend';
        $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $target_robot->update_session();

        // Increase or decrease this robot's attack to a value on the opposite side
        $temp_attack_function($this_robot, $this_ability, $temp_this_attack, $temp_target_attack);
        $this_robot->update_session();
        // Increase or decrease the target robot's attack to a value on the opposite side
        $temp_attack_function($target_robot, $this_ability, $temp_target_attack, $temp_this_attack);
        $target_robot->update_session();

        // Remove the ability attachment from this robot
        $this_robot->robot_frame = 'base';
        unset($this_robot->robot_attachments[$this_attachment_token]);
        $this_robot->update_session();
        // Remove the ability attachment from the target robot
        $target_robot->robot_frame = 'base';
        unset($target_robot->robot_attachments[$this_attachment_token]);
        $target_robot->update_session();


      } else {

        // Collect references to this and the target robot
        $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $this_player->values['robots_active'][$i]);
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $target_player->values['robots_active'][$i]);

        // Attach this ability attachment to this robot temporarily
        $temp_this_robot->robot_frame = 'defend';
        $temp_this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $temp_this_robot->update_session();
        // Attach this ability attachment to this robot temporarily
        $temp_target_robot->robot_frame = 'defend';
        $temp_target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $temp_target_robot->update_session();

        // Increase or decrease this robot's attack to a value on the opposite side
        $temp_attack_function($temp_this_robot, $this_ability, $temp_this_attack, $temp_target_attack);
        $temp_this_robot->update_session();
        // Increase or decrease the target robot's attack to a value on the opposite side
        $temp_attack_function($temp_target_robot, $this_ability, $temp_target_attack, $temp_this_attack);
        $temp_target_robot->update_session();

        // Remove the ability attachment from this robot
        $temp_this_robot->robot_frame = 'base';
        unset($temp_this_robot->robot_attachments[$this_attachment_token]);
        $temp_this_robot->update_session();
        // Remove the ability attachment from the target robot
        $temp_target_robot->robot_frame = 'base';
        unset($temp_target_robot->robot_attachments[$this_attachment_token]);
        $temp_target_robot->update_session();

      }

    }

    // Return true on success
    return true;

  }
  );
?>
