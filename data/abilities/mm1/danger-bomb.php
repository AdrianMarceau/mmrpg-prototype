<?
// DANGER BOMB
$ability = array(
  'ability_name' => 'Danger Bomb',
  'ability_token' => 'danger-bomb',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/006',
  'ability_master' => 'bomb-man',
  'ability_number' => 'DLN-006',
  'ability_description' => 'The user throws a dangerous and powerful bomb that explodes mid-air to inflict massive damage on the target! The user of this devasting attack receives {DAMAGE2}% recoil damage and benched team members on both sides are occasionally hit by the blast, so use with extreme caution.',
  'ability_type' => 'explode',
  'ability_energy' => 8,
  'ability_damage' => 50,
  'ability_damage2' => 20,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 70,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's first attachment token
    $this_attachment_token_one = 'ability_'.$this_ability->ability_token.'_one';
    $this_attachment_info_one = array(
    	'class' => 'ability',
      'sticky' => true,
    	'ability_token' => $this_ability->ability_token,
      'ability_frame' => 1,
      'ability_frame_animate' => array(1),
      'ability_frame_offset' => array('x' => 120, 'y' => 20, 'z' => 10)
      );

    // Define this ability's second attachment token
    $this_attachment_token_two = 'ability_'.$this_ability->ability_token.'_two';
    $this_attachment_info_two = array(
    	'class' => 'ability',
      'sticky' => true,
    	'ability_token' => $this_ability->ability_token,
      'ability_frame' => 2,
      'ability_frame_animate' => array(2),
      'ability_frame_offset' => array('x' => 270, 'y' => 5, 'z' => 10)
      );

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'throw',
      'kickback' => array(0, 0, 0),
      'success' => array(0, 160, 15, 10, $this_robot->print_robot_name().' throws the '.$this_ability->print_ability_name().'!'),
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // -- DAMAGE TARGET ROBOT -- //

    // Inflict damage on the opposing robot
    $target_robot->robot_attachments[$this_attachment_token_one] = $this_attachment_info_one;
    $target_robot->robot_attachments[$this_attachment_token_two] = $this_attachment_info_two;
    $target_robot->update_session();
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'frame' => 'damage',
      'kickback' => array(15, 0, 0),
      'success' => array(2, -30, 0, 10, $target_robot->print_robot_name().' was damaged by the blast!'),
      'failure' => array(2, -65, 0, -10, $target_robot->print_robot_name().' avoided the blast&hellip;')
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(2, -30, 0, 10, $target_robot->print_robot_name().' was invigorated by the blast!'),
      'failure' => array(2, -65, 0, -10, $target_robot->print_robot_name().' avoided the blast&hellip;')
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
    unset($target_robot->robot_attachments[$this_attachment_token_one]);
    unset($target_robot->robot_attachments[$this_attachment_token_two]);
    $target_robot->update_session();

    // Collect this first strike's damage to use as a base later
    $first_strike_ability_damage = $this_ability->ability_results['this_result'] != 'failure' && $this_ability->ability_results['this_amount'] > 0 ? $this_ability->ability_results['this_amount'] : 0;

    // If the first ability was a success, continue down the line
    if (!empty($first_strike_ability_damage)){

      // -- DAMAGE THIS ROBOT -- //

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'frame' => 'damage',
        'type' => $this_ability->ability_type,
        'percent' => true,
        'modifiers' => false,
        'kickback' => array(15, 0, 0),
        'success' => array(3, -30, 0, 10, $this_robot->print_robot_name().' was damaged by the blast!'),
        'failure' => array(3, -65, 0, -10, $this_robot->print_robot_name().' avoided the blast&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'type' => $this_ability->ability_type,
        'percent' => true,
        'modifiers' => false,
        'kickback' => array(0, 0, 0),
        'success' => array(3, -30, 0, 10, $this_robot->print_robot_name().' was invigorated by the blast!'),
        'failure' => array(3, -65, 0, -10, $this_robot->print_robot_name().' avoided the blast&hellip;')
        ));
      $energy_damage_amount = $first_strike_ability_damage;
      $energy_damage_amount += !empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0;
      $energy_damage_amount = round($energy_damage_amount * ($this_ability->ability_damage2 / 100));
      $this_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

      // -- RANDOM DAMAGE ALL BENCHED ROBOTS -- //

      // Collect backup active robots for this player
      $this_backup_robots_active = $this_player->values['robots_active'];
      $this_backup_robots_active_count = !empty($this_backup_robots_active) ? count($this_backup_robots_active) : 0;

      // Collect backup active robots for the target player
      $target_backup_robots_active = $this_player->values['robots_active'];
      $target_backup_robots_active_count = !empty($target_backup_robots_active) ? count($target_backup_robots_active) : 0;

      // Loop through any benched robots on either side and trigger damage maybe
      for ($key = 0; $key < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX; $key++){

        // Define the bench damage relative to the bench position times the recoil above
        $robot_bench_damage_amount = ceil($first_strike_ability_damage / ($key + 1));

        // If a robot on the target side exists at the given key
        if (isset($target_backup_robots_active[$key])){

          // Collect a reference to the robot and create the necessary data
          $info = $target_backup_robots_active[$key];
          if ($info['robot_id'] == $target_robot->robot_id){ continue; }
          if (!$this_battle->critical_chance(ceil((9 - $info['robot_key']) * 10))){ break; }
          $this_ability->ability_results_reset();
          $temp_target_robot = new mmrpg_robot($this_battle, $this_player, $info);
          // Update the ability options text
          $this_ability->damage_options_update(array(
            'success' => array(2, -20, -5, -5, $temp_target_robot->print_robot_name().' was damaged by the blast!'),
            'failure' => array(3, 0, 0, -9999, ''),
            'options' => array('apply_modifiers' => false)
            ));
          $this_ability->recovery_options_update(array(
            'success' => array(2, -20, -5, -5, $temp_target_robot->print_robot_name().' was refreshed by the blast!'),
            'failure' => array(3, 0, 0, -9999, ''),
            'options' => array('apply_modifiers' => false)
            ));
          $energy_damage_amount = $robot_bench_damage_amount;
          $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
          if ($this_ability->ability_results['this_result'] == 'failure'){ break; }

        }

        // If a robot on this side exists at the given key
        if (isset($this_backup_robots_active[$key])){

          // Collect a reference to the robot and create the necessary data
          $info = $this_backup_robots_active[$key];
          if ($info['robot_id'] == $this_robot->robot_id){ continue; }
          if (!$this_battle->critical_chance(ceil((9 - $info['robot_key']) * 10))){ break; }
          $this_ability->ability_results_reset();
          $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
          // Update the ability options text
          $this_ability->damage_options_update(array(
            'success' => array(2, -20, -5, -5, $temp_this_robot->print_robot_name().' was damaged by the blast!'),
            'failure' => array(3, 0, 0, -9999, ''),
            'options' => array('apply_modifiers' => false)
            ));
          $this_ability->recovery_options_update(array(
            'success' => array(2, -20, -5, -5, $temp_this_robot->print_robot_name().' was refreshed by the blast!'),
            'failure' => array(3, 0, 0, -9999, ''),
            'options' => array('apply_modifiers' => false)
            ));
          $energy_damage_amount = $robot_bench_damage_amount;
          $temp_this_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
          if ($this_ability->ability_results['this_result'] == 'failure'){ break; }

        }

      }

      // If this robot is no longer active, find a new active robot for this player
      $this_active_robot = $this_robot;
      if ($this_robot->robot_energy < 1 || $this_robot->robot_status == 'disabled'){
        foreach ($this_player->values['robots_active'] AS $key => $info){
          if ($info['robot_position'] != 'bench'){
              $this_active_robot = new mmrpg_robot($this_battle, $this_player, array('robot_id' => $info['robot_id'], 'robot_token' => $info['robot_token']));
            }
        }
      }

      // Trigger the disabled event on these robots now if necessary
      if (!empty($this_backup_robots_active)){
        foreach ($this_backup_robots_active AS $key => $info){
          if ($info['robot_id'] == $this_robot->robot_id){ continue; }
          $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
          if ($temp_this_robot->robot_energy <= 0 || $temp_this_robot->robot_status == 'disabled'){ $temp_this_robot->trigger_disabled($this_robot, $this_ability); }
        }
      }

      // Trigger the disabled event on this robot now if necessary
      if ($this_robot->robot_energy < 1 || $this_robot->robot_status == 'disabled'){
        $this_robot->trigger_disabled($target_robot, $this_ability);
      }

      // Trigger the disabled event on the targets now if necessary
      if (!empty($target_backup_robots_active)){
        foreach ($target_backup_robots_active AS $key => $info){
          if ($info['robot_id'] == $this_robot->robot_id){ continue; }
          $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
          if ($temp_this_robot->robot_energy <= 0 || $temp_this_robot->robot_status == 'disabled'){ $temp_this_robot->trigger_disabled($this_robot, $this_ability); }
        }
      }

      // Trigger the disabled event on the target robot now if necessary
      if ($target_robot->robot_energy < 1 || $target_robot->robot_status == 'disabled'){
        $target_robot->trigger_disabled($this_active_robot, $this_ability);
      }

    }
    // Otherwise, if the ability missed or was absored somehow, treat as attack from target
    else {

      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'frame' => 'damage',
        'type' => '',
        'kickback' => array(15, 0, 0),
        //'success' => array(3, -30, 0, 10, $this_robot->print_robot_name().' was damaged by the blast!'),
        'success' => array(3, -65, 0, -10, $this_robot->print_robot_name().' avoided the blast&hellip;'),
        'failure' => array(3, -65, 0, -10, $this_robot->print_robot_name().' avoided the blast&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'type' => '',
        'kickback' => array(0, 0, 0),
        //'success' => array(3, -30, 0, 10, $this_robot->print_robot_name().' was invigorated by the blast!'),
        'success' => array(3, -65, 0, -10, $this_robot->print_robot_name().' avoided the blast&hellip;'),
        'failure' => array(3, -65, 0, -10, $this_robot->print_robot_name().' avoided the blast&hellip;')
        ));
      $energy_damage_amount = 0;
      $this_robot->trigger_damage($target_robot, $this_ability, $energy_damage_amount, false);

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