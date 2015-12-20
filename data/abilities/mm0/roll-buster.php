<?
// ROLL BUSTER
$ability = array(
  'ability_name' => 'Roll Buster',
  'ability_token' => 'roll-buster',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Roll',
  'ability_description' => 'The user charges on the first turn to build power and recover life energy by {RECOVERY2}%, then releases a powerful shot on the second to inflict {DAMAGE}% damage to shield sytems!  This ability\'s charge can be held indefinitely and will continue boosting the user\'s energy for as long as it is attached.',
  'ability_energy' => 4,
  'ability_damage' => 36,
  'ability_damage_percent' => true,
  'ability_recovery2' => 10,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
    	'class' => 'ability',
      'ability_id' => $this_ability->ability_id,
    	'ability_token' => $this_ability->ability_token,
      'ability_frame' => 0,
      'ability_frame_animate' => array(1, 2, 1, 0),
      'ability_frame_offset' => array('x' => -10, 'y' => -10, 'z' => -20),
    	'attachment_energy' => 0,
    	'attachment_energy_base_percent' => 0,
    	'attachment_repeat' => array(
        'kind' => 'energy',
        'trigger' => 'recovery',
        'type' => '',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'taunt',
        'rates' => array(100, 0, 0),
        'success' => array(2, -10, 0, -10, 'The '.$this_ability->print_name().' charge repaired internal systems!'),
        'failure' => array(2, -10, 0, -10, ''),
        'options' => array(
          'referred_recovery' => true,
          'referred_player' => array('player_token' => $this_player->player_token, 'player_id' => $this_player->player_id),
          'referred_robot' => array('robot_token' => $this_robot->robot_token, 'robot_id' => $this_robot->robot_id),
          'referred_energy' => $this_robot->robot_energy,
          'referred_attack' => $this_robot->robot_attack,
          'referred_defense' => $this_robot->robot_defense,
          'referred_speed' => $this_robot->robot_speed
          )
        )
      );
    // Loop through each existing attachment and alter the start frame by one
    foreach ($this_robot->robot_attachments AS $key => $info){ array_push($this_attachment_info['ability_frame_animate'], array_shift($this_attachment_info['ability_frame_animate'])); }

    // Define the charge required flag based on existing attachments of this ability
    $this_charge_required = !isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;
    // If this robot is holding a Charge Module, bypass changing and set to false
    if ($this_robot->robot_item == 'item-charge-module'){ $this_charge_required = false; }

    // If the ability flag was not set, this ability begins charging
    if ($this_charge_required){

      // Target this robot's self
      $this_ability->target_options_update(array(
        'frame' => 'defend',
        'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

      // Define the energy mod amount for this ability based on it's recovery
      $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_recovery2 / 100));
      $this_attachment_info['attachment_energy'] = $energy_recovery_amount;
      $this_attachment_info['attachment_energy_base_percent'] = $this_ability->ability_recovery2;

      // Attach this ability attachment to the robot using it
      $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

    }
    // Else if the ability flag was set, the ability is released at the target
    else {

      // Remove this ability attachment to the robot using it
      $this_robot->unset_attachment($this_attachment_token);

      // Update this ability's target options and trigger
      $this_ability->target_options_update(array(
        'frame' => 'shoot',
        'kickback' => array(-5, 0, 0),
        'success' => array(3, 100, -15, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!'),
        ));
      $this_robot->trigger_target($target_robot, $this_ability);

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'defense',
        'percent' => true,
        'modifiers' => false,
        'kickback' => array(20, 0, 0),
        'success' => array(3, -110, -15, 10, 'A massive energy shot hit the target!'),
        'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed&hellip;')
        ));
      $defense_damage_amount = ceil($target_robot->robot_base_defense * ($this_ability->ability_damage / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount, false, array('apply_modifiers' => false));

    }

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
    if (!$this_charge_required){ $this_ability->set_energy(0); }
    // Otherwise, return the weapon energy back to default
    else { $this_ability->reset_energy(); }

    // If this robot is holding a Charge Module, bypass changing but reduce the power of the ability
    if ($this_robot->robot_item == 'item-charge-module'){
      $this_charge_required = false;
      $temp_item_info = rpg_ability::get_index_info($this_robot->robot_item);
      $this_ability->set_damage(ceil($this_ability->ability_base_damage * ($temp_item_info['ability_damage2'] / $temp_item_info['ability_recovery2'])));
    } else {
      $this_ability->reset_damage();
    }

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){
      if (!$this_charge_required){ $this_ability->set_target('select_target'); }
      else { $this_ability->reset_target(); }
    } else {
      $this_ability->reset_target();
    }

    // Return true on success
    return true;

    }
  );
?>