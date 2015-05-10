<?
// LEAF SHIELD
$ability = array(
  'ability_name' => 'Leaf Shield',
  'ability_token' => 'leaf-shield',
  'ability_game' => 'MM02',
  'ability_group' => 'MM02/Weapons/016',
  'ability_master' => 'wood-man',
  'ability_number' => 'DWN-016',
  'ability_description' => 'The user surrounds itself with sharp, leaf-like blades to bolster shields and reduce all damage by 50%! The shield can also be thrown at the target for massive damage!',
  'ability_type' => 'nature',
  'ability_type2' => 'shield',
  'ability_energy' => 8,
  'ability_damage' => 38,
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
    	//'attachment_duration' => 3,
      'attachment_damage_breaker' => 0.50,
      //'attachment_defense' => 0,
    	'attachment_weaknesses' => array('flame', 'cutter'),
    	'attachment_create' => array(
        'kind' => 'special',
        'type' => '',
        'type2' => '',
        'percent' => true,
        'frame' => 'taunt',
        'rates' => array(100, 0, 0),
        'success' => array(1, -10, 0, -10, $this_robot->print_robot_name().'&#39;s shields were bolstered!'),
        'failure' => array(1, -10, 0, -10, $this_robot->print_robot_name().'&#39;s shields were bolstered!')
        ),
    	'attachment_destroy' => array(
        'kind' => 'special',
        'type' => '',
        'type2' => '',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'rates' => array(100, 0, 0),
        'success' => array(2, -2, 0, -10,  'The '.$this_ability->print_ability_name().'&#39;s protection was lost&hellip;'),
        'failure' => array(2, -2, 0, -10,  'The '.$this_ability->print_ability_name().'&#39;s protection was lost&hellip;')
        ),
      'ability_frame' => 0,
      'ability_frame_animate' => array(0, 1),
      'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
      );

    // If the ability flag was not set, leaf shield raises defense by 30%
    if (!isset($this_robot->robot_attachments[$this_attachment_token])){

      // Define the defense mod amount for this ability
      $this_attachment_info['attachment_defense'] = ceil($this_robot->robot_defense * ($this_ability->ability_recovery2 / 100));
      if (($this_robot->robot_defense + $this_attachment_info['attachment_defense']) > MMRPG_SETTINGS_STATS_MAX){ $this_attachment_info['attachment_defense'] = 9999 - $this_robot->robot_defense; }

      // Target this robot's self
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array(0, -10, 0, -10, $this_robot->print_robot_name().' raises a '.$this_ability->print_ability_name().'!')
        ));
      $this_robot->trigger_target($this_robot, $this_ability);

      // Increase this robot's defense stat
      $this_ability->damage_options_update($this_attachment_info['attachment_create'], true);
      $this_ability->recovery_options_update($this_attachment_info['attachment_create'], true);
      //$defense_recovery_amount = $this_attachment_info['attachment_defense']; //ceil($this_robot->robot_defense * ($this_ability->ability_recovery / 100));
      //$this_robot->trigger_recovery($this_robot, $this_ability, $defense_recovery_amount);

      // Attach this ability attachment to the robot using it
      //$this_attachment_info['attachment_defense'] = $this_ability->ability_results['this_amount'];
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();

    }
    // Else if the ability flag was set, leaf shield is thrown and defense is lowered by 30%
    else {

      // Collect the attachment from the robot to back up its info
      $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
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
      $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
      $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
      //$defense_damage_amount = $this_attachment_info['attachment_defense']; //ceil($this_robot->robot_defense * ($this_ability->ability_recovery / 100));
      //$trigger_options = array('apply_modifiers' => false);
      //$this_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount, true, $trigger_options);

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

    // If the ability flag had already been set, reduce the weapon energy to zero
    if (isset($this_robot->robot_attachments[$this_attachment_token])){ $this_ability->ability_energy = 0; }
    // Otherwise, return the weapon energy back to default
    else { $this_ability->ability_energy = $this_ability->ability_base_energy; }
    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>