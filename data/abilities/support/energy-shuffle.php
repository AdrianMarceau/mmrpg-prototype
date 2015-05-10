<?
// ENERGY SHUFFLE
$ability = array(
  'ability_name' => 'Energy Shuffle',
  'ability_token' => 'energy-shuffle',
  'ability_game' => 'MMRPG',
  'ability_description' => 'The user triggers a dangerous glitch in the prototype that shuffles the life energy of all robots on the field!',
  'ability_energy' => 8,
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
    
    // Attach this ability attachment to this robot temporarily
    //$this_robot->robot_frame = 'defend';
    //$this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    //$this_robot->update_session();
    
    // Attach this ability attachment to this robot temporarily
    $target_robot->robot_frame = 'defend';
    $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    $target_robot->update_session();
    
    // Attach this ability to all robots on this player's side of the field
    $backup_robots_active = $this_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the this's benched robots, inflicting les and less damage to each
      $this_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $this_robot->robot_id){ continue; }
        $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
        // Attach this ability attachment to the this robot temporarily
        $temp_this_robot->robot_frame = 'defend';
        $temp_this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $temp_this_robot->update_session();
        $this_key++;
      }
    }
    
    // Attach this ability to all robots on the target's side of the field
    $backup_robots_active = $target_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the target's benched robots, inflicting les and less damage to each
      $target_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
        // Attach this ability attachment to the target robot temporarily
        $temp_target_robot->robot_frame = 'defend';
        $temp_target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $temp_target_robot->update_session();
        $target_key++;
      }
    }
    
    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 0, 10, -10, $this_robot->print_robot_name().' triggered an '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);
    
    // Remove this attachment from the robot
    //$this_robot->robot_frame = 'base';
    //unset($this_robot->robot_attachments[$this_attachment_token]);
    //$this_robot->update_session();
    
    // Remove this attachment from the target robot
    $target_robot->robot_frame = 'base';
    unset($target_robot->robot_attachments[$this_attachment_token]);
    $target_robot->update_session();
      
    // Remove this ability from all robots on this player's side of the field
    $backup_robots_active = $this_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the this's benched robots, inflicting les and less damage to each
      $this_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $this_robot->robot_id){ continue; }
        $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
        // Attach this ability attachment to the this robot temporarily
        unset($temp_this_robot->robot_attachments[$this_attachment_token]);
        $temp_this_robot->update_session();
        $this_key++;
      }
    }
    
    // Remove this ability from all robots on the target's side of the field
    $backup_robots_active = $target_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the target's benched robots, inflicting les and less damage to each
      $target_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
        // Attach this ability attachment to the target robot temporarily
        unset($temp_target_robot->robot_attachments[$this_attachment_token]);
        $temp_target_robot->update_session();
        $target_key++;
      }
    }
    
    
    /*
     * ACTUALLY DEAL DAMAGE / RECOVERY
     */
    
    // Create a function that increases or decreases a robot's energy to target
    $temp_energy_function = function($this_robot, $this_ability, $temp_target_energy){
      global $this_battle;
      
      // Collect the target's current energy amount
      $temp_this_energy = $this_robot->robot_energy.'/'.$this_robot->robot_base_energy;
      
      // Only continue if this robot and the target's energy are not equal
      if ($temp_this_energy != $temp_target_energy){

        // Break apart the energy into its current and base amounts
        list($temp_energy, $temp_base_energy) = explode('/', $temp_target_energy);
        
        // Update this robot's values with the random data
        $this_robot->robot_energy = $temp_energy;
        $this_robot->robot_base_energy = $temp_base_energy;
        $this_robot->update_session();
        
        // Target this robot's self
        $is_her = in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm', 'splash-woman')) ? true : false;
        $is_mecha = $this_robot->robot_class == 'mecha' ? true : false;
        $this_ability->target_options_update(array(
          'frame' => 'defend',
          'success' => array(9, 0, 10, -10, $this_robot->print_robot_name().'&#39;s life energy was modified&hellip;<br /> '.($is_her ? 'Her' : ($is_mecha ? 'Its' : 'His')).' new energy stats are '.$this_robot->print_robot_energy().' / '.$this_robot->print_robot_base_energy().'!')
          ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));
        
      }
      // Otherwise, if the two already have equal energy amounts
      else {
        
        // Target this robot's self and show the ability failing
        $this_ability->target_options_update(array(
          'frame' => 'defend',
          'success' => array(9, 0, 0, -10, $this_robot->print_robot_name().'&#39;s life energy was not affected&hellip;')
          ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));
        
        // Return true on success (well, failure, but whatever)
        return true;
        
      }
            
    };
      
    // Collect energy amounts for all robots on the field into an array and shuffle
    $temp_energy_key = 0;
    $temp_energy_options = array();
    foreach ($this_player->values['robots_active'] AS $info){ $temp_energy_options[] = $info['robot_energy'].'/'.$info['robot_base_energy']; }
    foreach ($target_player->values['robots_active'] AS $info){ $temp_energy_options[] = $info['robot_energy'].'/'.$info['robot_base_energy']; }
    shuffle($temp_energy_options);
    
    // Define a quick function for pulling a random element from the options array
    $temp_option_function = function(&$temp_energy_options, $current_energy){
      foreach ($temp_energy_options AS $key => $temp_option){
        if ($temp_option != $current_energy){
          unset($temp_energy_options[$key]);
          $temp_energy_options = array_values($temp_energy_options);
          return $temp_option;
        }
      }
      $temp_option = array_shift($temp_energy_options);
      return $temp_option;
    };
        
    // Increase or decrease this robot's energy to a random target
    $temp_energy_function($this_robot, $this_ability, $temp_option_function($temp_energy_options, $this_robot->robot_energy.'/'.$this_robot->robot_base_energy));
    $this_robot->update_session();
    
    // Remove this ability from all robots on this player's side of the field
    $backup_robots_active = $this_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the this's benched robots, inflicting les and less damage to each
      $this_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        if ($info['robot_id'] == $this_robot->robot_id){ continue; }
        $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
        $temp_energy_function($temp_this_robot, $this_ability, $temp_option_function($temp_energy_options, $temp_this_robot->robot_energy.'/'.$temp_this_robot->robot_base_energy));
        $temp_this_robot->update_session();
        $this_key++;
      }
    }
    
    // Remove this ability from all robots on the target's side of the field
    $backup_robots_active = $target_player->values['robots_active'];
    $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
    if ($backup_robots_active_count > 0){
      // Loop through the target's benched robots, inflicting les and less damage to each
      $target_key = 0;
      foreach ($backup_robots_active AS $key => $info){
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
        $temp_energy_function($temp_target_robot, $this_ability, $temp_option_function($temp_energy_options, $temp_target_robot->robot_energy.'/'.$temp_target_robot->robot_base_energy));
        $temp_target_robot->update_session();
        $target_key++;
      }
    }
    
    // Return true on success
    return true;
      
  }
  );
?>