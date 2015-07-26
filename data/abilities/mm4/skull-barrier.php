<?
// SKULL BARRIER
$ability = array(
  'ability_name' => 'Skull Barrier',
  'ability_token' => 'skull-barrier',
  'ability_game' => 'MM04',
  'ability_group' => 'MM04/Weapons/032',
  'ability_master' => 'skull-man',
  'ability_number' => 'DCN-032',
  'ability_description' => 'The user surrounds itself with tiny, skull-like barriers to bolster shields prevent all damage from attacks for one turn! The shield can also be thrown at the target for massive damage!',
  'ability_type' => 'shadow',
  'ability_type2' => 'shield',
  'ability_energy' => 8,
  'ability_damage' => 32,
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
    	'attachment_duration' => 1,
      'attachment_damage_breaker' => 0.0,
    	'attachment_weaknesses' => array('crystal', 'laser'),
    	'attachment_create' => array(
        'trigger' => 'special',
        'kind' => '',
        'percent' => true,
        'frame' => 'taunt',
        'rates' => array(100, 0, 0),
        'success' => array(1, -10, 0, -10, 'The '.$this_ability->print_ability_name().' attached itself to '.$this_robot->print_robot_name().'!<br /> '.$this_robot->print_robot_name().'&#39;s defenses were bolstered!'),
        'failure' => array(1, -10, 0, -10, 'The '.$this_ability->print_ability_name().' attached itself to '.$this_robot->print_robot_name().'!<br /> '.$this_robot->print_robot_name().'&#39;s defenses were bolstered!')
        ),
    	'attachment_destroy' => array(
        'trigger' => 'special',
        'kind' => '',
        'type' => '',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'rates' => array(100, 0, 0),
        'success' => array(9, -10, 0, -10,  'The '.$this_ability->print_ability_name().' faded away!<br /> '.$this_robot->print_robot_name().' is no longer protected&hellip;'),
        'failure' => array(9, -10, 0, -10, 'The '.$this_ability->print_ability_name().' faded away!<br /> '.$this_robot->print_robot_name().' is no longer protected&hellip;')
        ),
      'ability_frame' => 0,
      'ability_frame_animate' => array(2, 3, 4, 5, 0, 1),
      'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
      );

    // Define the charge required flag based on existing attachments of this ability
    $this_charge_required = !isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;
    // If this robot is holding a Charge Module, bypass changing and set to false
    if ($this_robot->robot_item == 'item-charge-module'){ $this_charge_required = false; }

    // If the ability flag was not set, skull barrier cuts damage by half
    if ($this_charge_required){

      // Target this robot's self
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(0, -10, 0, -10, $this_robot->print_robot_name().' raises a '.$this_ability->print_ability_name().'!')
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

      // Increase this robot's defense stat
      $this_ability->target_options_update($this_attachment_info['attachment_create'], true);
      $this_robot->trigger_target($this_robot, $this_ability);

      // Attach this ability attachment to the robot using it
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();

    }
    // Else if the ability flag was set, leaf shield is thrown and defense is lowered by 30%
    else {

      // Collect the attachment from the robot to back up its info
      $this_attachment_info = isset($this_robot->robot_attachments[$this_attachment_token]) ? $this_robot->robot_attachments[$this_attachment_token] : $this_attachment_info;
      // Remove this ability attachment to the robot using it
      unset($this_robot->robot_attachments[$this_attachment_token]);
      $this_robot->update_session();

      // Target the opposing robot
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(0, 85, -10, -10, $this_robot->print_robot_name().' releases the '.$this_ability->print_ability_name().'!')
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(5, 0, 0),
        'success' => array(1, -75, 0, -10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
        'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(1, -75, 0, -10, 'The '.$this_ability->print_ability_name().' crashed into the target!'),
        'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;')
        ));
      $energy_damage_amount = $this_ability->ability_damage;
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

      // Decrease this robot's defense stat
      $this_ability->target_options_update($this_attachment_info['attachment_destroy']);
      $this_robot->trigger_target($this_robot, $this_ability);

    }

    // Either way, update this ability's settings to prevent recovery
    $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->update_session();


    // Return true on success
    return true;

  },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;

    // Define the charge required flag based on existing attachments of this ability
    $this_charge_required = !isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

    // If the ability flag had already been set, reduce the weapon energy to zero
    if (!$this_charge_required){ $this_ability->ability_energy = 0; }
    // Otherwise, return the weapon energy back to default
    else { $this_ability->ability_energy = $this_ability->ability_base_energy; }

    // If this robot is holding a Charge Module, bypass changing but reduce the power of the ability
    if ($this_robot->robot_item == 'item-charge-module'){
      $this_charge_required = false;
      $temp_item_info = mmrpg_ability::get_index_info($this_robot->robot_item);
      $this_ability->ability_damage = ceil($this_ability->ability_base_damage * ($temp_item_info['ability_damage2'] / $temp_item_info['ability_recovery2']));
    } else {
      $this_ability->ability_damage = $this_ability->ability_base_damage;
    }

    // Define the allow targetting flag based on if the user's type matches the ability
    $this_allow_target_select = !empty($this_robot->robot_core) && $this_robot->robot_core == $this_ability->ability_type ? true : false;

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){ $this_allow_target_select = true; }

    // If this ability is being used by a robot with the same core type AND it's already summoned, allow targetting
    if (!$this_charge_required && $this_allow_target_select){

      // Update this ability's targetting setting
      $this_ability->ability_target = 'select_target';
      $this_ability->update_session();

    }
    // Else if the ability attachment is not there, change the target back to auto
    else {

      // Update this ability's targetting setting
      $this_ability->ability_target = 'auto';
      $this_ability->update_session();

    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>