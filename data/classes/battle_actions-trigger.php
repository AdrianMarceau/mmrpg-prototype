<?

// Reload all variables to ensure values are fresh
$this_player->player_load(array('player_id' => $this_player->player_id, 'player_token' => $this_player->player_token));
$target_player->player_load(array('player_id' => $target_player->player_id, 'player_token' => $target_player->player_token));
$this_robot->robot_load(array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token));
$target_robot->robot_load(array('robot_id' => $target_robot->robot_id, 'robot_token' => $target_robot->robot_token));
$this_player->update_session();
$target_robot->update_session();
$this_robot->update_session();
$target_robot->update_session();

// Create the action array in the history object if not exist
if (!isset($this_player->history['actions'])){
  $this_player->history['actions'] = array();
}

// Update the session with recent changes
$this_player->update_session();

// If the target player does not have any robots left
if ($target_player->counters['robots_active'] == 0){

  // Trigger the battle complete action to update status and result
  $this->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_token);

}


// Start the battle loop to allow breaking
$battle_loop = true;
while ($battle_loop == true && $this->battle_status != 'complete'){

  // If the battle is just starting
  if ($this_action == 'start'){
    // If the target player is hidden
    if ($this_player->player_token == 'player'){

      // Create the enter event for this robot
      $event_header = $this_robot->robot_name;
      $event_body = "{$this_robot->print_robot_name()} wants to fight!<br />";
      $this_robot->robot_frame = 'defend';
      $this_robot->robot_frame_styles = '';
      $this_robot->robot_detail_styles = '';
      $this_robot->robot_position = 'active';
      if (isset($this_robot->robot_quotes['battle_start'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_robot->print_robot_quote('battle_start', $this_find, $this_replace);
      }
      $this_robot->update_session();
      $this_player->update_session();
      $this->events_create($this_robot, false, $event_header, $event_body, array('canvas_show_target' => false, 'console_show_target' => false));

      // Create an event for this robot teleporting in
      if ($this_player->counters['robots_active'] == 1){
        $this_robot->robot_frame = 'taunt';
        $this_robot->update_session();
        $this->events_create(false, false, '', '');
      }
      $this_robot->robot_frame = 'base';
      $this_robot->robot_frame_styles = '';
      $this_robot->robot_detail_styles = '';
      $this_robot->update_session();

    }

    // Show the player's other robots one by one
    foreach ($this_player->values['robots_active'] AS $key => $info){
      if (!preg_match('/display:\s?none;/i', $info['robot_frame_styles'])){ continue; }
      if ($this_robot->robot_id == $info['robot_id']){
        $this_robot->robot_frame = 'taunt';
        $this_robot->robot_frame_styles = '';
        $this_robot->robot_detail_styles = '';
        $this_robot->update_session();
        $this->events_create(false, false, '', '');
        $this_robot->robot_frame = 'base';
        $this_robot->update_session();
      } else {
        $temp_robot = new mmrpg_robot($this, $this_player, $info);
        $temp_robot->robot_frame = 'taunt';
        $temp_robot->robot_frame_styles = '';
        $temp_robot->robot_detail_styles = '';
        $temp_robot->update_session();
        $this->events_create(false, false, '', '');
        $temp_robot->robot_frame = 'base';
        $temp_robot->update_session();
      }
    }

    // Ensure this robot has abilities to loop through
    if (!isset($this_robot->flags['ability_startup']) && !empty($this_robot->robot_abilities)){
      // Loop through each of this robot's abilities and trigger the start event
      $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
      foreach ($this_robot->robot_abilities AS $this_key => $this_token){
        // Define the current ability object using the loaded ability data
        $temp_abilityinfo = mmrpg_ability::parse_index_info($temp_abilities_index[$this_token]);
        $temp_ability = new mmrpg_ability($this, $this_player, $this_robot, $temp_abilityinfo);
        // Update or create this abilities session object
        $temp_ability->update_session();
      }
      // And now update the robot with the flag
      $this_robot->flags['ability_startup'] = true;
      $this_robot->update_session();
    }

    // Set this token to the ID and token of the starting robot
    $this_token = $this_robot->robot_id.'_'.$this_robot->robot_token;

    // Return from the battle function with the start results
    $this_return = true;
    break;

  }
  // Else if the player has chosen to use an ability
  elseif ($this_action == 'ability'){

    // Combine into the actions index
    $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

    // DEFINE ABILITY TOKEN

    // If an ability token was not collected
    if (empty($this_token)){
      // Collect the ability choice from the robot
      $temp_token = mmrpg_robot::robot_choices_abilities(array(
        'this_battle' => &$this,
        'this_field' => &$this->battle_field,
        'this_player' => &$this_player,
        'this_robot' => &$this_robot,
        'target_player' => &$target_player,
        'target_robot' => &$target_robot
        ));
      $temp_id = array_search($temp_token, $this_robot->robot_abilities);
      if (empty($temp_id)){ $temp_id = $this->index['abilities'][$temp_token]['ability_id']; }
      $temp_id = $this_robot->robot_id.str_pad($temp_id, '3', '0', STR_PAD_LEFT);
      //$this_token = array('ability_id' => $temp_id, 'ability_token' => $temp_token);
      $this_token = mmrpg_ability::parse_index_info($temp_abilities_index[$temp_token]);
      $this_token['ability_id'] = $temp_id;
    }
    // Otherwise, parse the token for data
    else {
      // Define the ability choice data for this robot
      list($temp_id, $temp_token) = explode('_', $this_token);
      $this_token = mmrpg_ability::parse_index_info($temp_abilities_index[$temp_token]);
      $this_token['ability_id'] = $temp_id;
    }

    // If the current robot has been already disabled
    if ($this_robot->robot_status == 'disabled'){
      // Break from this queued action as the robot cannot fight
      break;
    }

    // Define the current ability object using the loaded ability data
    $this_ability = new mmrpg_ability($this, $this_player, $this_robot, $this_token);
    // Trigger this robot's ability
    $this_ability->ability_results = $this_robot->trigger_ability($target_robot, $this_ability);

    // Ensure the battle has not completed before triggering the taunt event
    if ($this->battle_status != 'complete'){
      // Check to ensure this robot hasn't taunted already
      if (!isset($this_robot->flags['robot_quotes']['battle_taunt'])
        && isset($this_robot->robot_quotes['battle_taunt'])
        && $this_robot->robot_quotes['battle_taunt'] != '...'
        && $this_ability->ability_results['this_amount'] > 0
        && $target_robot->robot_status != 'disabled'
        && $this->critical_chance(3)){
        // Generate this robot's taunt event after dealing damage, which only happens once per battle
        $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_taunt']);
        $event_body = ($this_player->player_token != 'player' ? $this_player->print_player_name().'&#39;s ' : '').$this_robot->print_robot_name().' taunts the opponent!<br />';
        $event_body .= $this_robot->print_robot_quote('battle_taunt', $this_find, $this_replace);
        //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
        $this_robot->robot_frame = 'taunt';
        $target_robot->robot_frame = 'base';
        $this->events_create($this_robot, $target_robot, $event_header, $event_body, array('console_show_target' => false));
        $this_robot->robot_frame = 'base';
        // Create the quote flag to ensure robots don't repeat themselves
        $this_robot->flags['robot_quotes']['battle_taunt'] = true;
      }

    }

    // Set this token to the ID and token of the triggered ability
    $this_token = $this_token['ability_id'].'_'.$this_token['ability_token'];

    // Return from the battle function with the used ability
    $this_return = &$this_ability;
    break;

  }
  // Else if the player has chosen to switch
  elseif ($this_action == 'switch'){

    // Collect this player's last action if it exists
    if (!empty($this_player->history['actions'])){
      $this_recent_switches = array_slice($this_player->history['actions'], -5, 5, false);
      foreach ($this_recent_switches AS $key => $info){
        if ($info['this_action'] == 'switch' || $info['this_action'] == 'start'){ $this_recent_switches[$key] = $info['this_action_token']; } //$info['this_action_token'];
        else { unset($this_recent_switches[$key]); }
      }
      $this_recent_switches = array_values($this_recent_switches);
      $this_recent_switches_count = count($this_recent_switches);
    }
    // Otherwise define an empty action
    else {
      $this_recent_switches = array();
      $this_recent_switches_count = 0;
    }

    // If the robot token was not collected and this player is NOT on autopilot
    if (empty($this_token) && $this_player->player_side == 'left'){
      // Clear any pending actions
      $this->actions_empty();
      // Return from the battle function
      $this_return = true;
      break;

    }
    // Else If a robot token was not collected and this player IS on autopilot
    elseif (empty($this_token) && $this_player->player_side == 'right'){
      // Decide which robot the target should use (random)
      $active_robot_count = count($this_player->values['robots_active']);
      if ($active_robot_count == 1){
        $this_robotinfo = $this_player->values['robots_active'][0];
      }
      elseif ($active_robot_count > 1) {
        $this_current_token = $this_robot->robot_id.'_'.$this_robot->robot_token;
        do {
          $this_robotinfo = $this_player->values['robots_active'][mt_rand(0, ($active_robot_count - 1))];
          if ($this_robotinfo['robot_id'] == $this_robot->robot_id ){ continue; }
          $this_temp_token = $this_robotinfo['robot_id'].'_'.$this_robotinfo['robot_token'];
        } while(empty($this_temp_token));
      }
      else {
        $this_robotinfo = array('robot_id' => 0, 'robot_token' => 'robot');
      }
      //$this->events_create(false, false, 'DEBUG', 'auto switch picked ['.print_r($this_robotinfo['robot_name'], true).'] | recent : ['.preg_replace('#\s+#', ' ', print_r($this_recent_switches, true)).']');
    }
    // Otherwise, parse the token for data
    else {
      list($temp_id, $temp_token) = explode('_', $this_token);
      $this_robotinfo = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
    }

    //$this->events_create(false, false, 'DEBUG', 'switch picked ['.print_r($this_robotinfo['robot_token'], true).'] | other : []');

    // Update this player and robot's session data before switching
    $this_player->update_session();
    $this_robot->update_session();

    // Define the switch reason based on if this robot is disabled
    $this_switch_reason = $this_robot->robot_status != 'disabled' ? 'withdrawn' : 'removed';

    /*
    $this->events_create(false, false, 'DEBUG',
    	'$this_switch_reason = '.$this_switch_reason.'<br />'.
      '$this_player->values[\'current_robot\'] = '.$this_player->values['current_robot'].'<br />'.
      '$this_player->values[\'current_robot_enter\'] = '.$this_player->values['current_robot_enter'].'<br />'.
      '');
    */

    // If this robot is being withdrawn on the same turn it entered, return false
    if ($this_player->player_side == 'right' && $this_switch_reason == 'withdrawn' && $this_player->values['current_robot_enter'] == $this->counters['battle_turn']){
      // Return false to cancel the switch action
      return false;
    }

    // If the switch reason was removal, make sure this robot stays hidden
    if ($this_switch_reason == 'removed' && $this_player->player_side == 'right'){
      $this_robot->flags['hidden'] = true;
      $this_robot->update_session();
    }

    // Withdraw the player's robot and display an event for it
    if ($this_robot->robot_position != 'bench'){
      $this_robot->robot_frame = $this_robot->robot_status != 'disabled' ? 'base' : 'defeat';
      $this_robot->robot_position = 'bench';
      $this_player->player_frame = 'base';
      $this_player->values['current_robot'] = false;
      $this_player->values['current_robot_enter'] = false;
      $this_robot->update_session();
      $this_player->update_session();
      $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
      $event_body = $this_robot->print_robot_name().' is '.$this_switch_reason.' from battle!';
      if ($this_robot->robot_status != 'disabled' && isset($this_robot->robot_quotes['battle_retreat'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_robot->print_robot_quote('battle_retreat', $this_find, $this_replace);
      }
      // Only show the removed event or the withdraw event if there's more than one robot
      if ($this_switch_reason == 'removed' || $this_player->counters['robots_active'] > 1){
        $this->events_create($this_robot, false, $event_header, $event_body, array('canvas_show_disabled_bench' => $this_robot->robot_id.'_'.$this_robot->robot_token));
      }
      $this_robot->update_session();
    }

    // If the switch reason was removal, hide the robot from view
    if ($this_switch_reason == 'removed'){
      $this_robot->flags['hidden'] = true;
      $this_robot->update_session();
    }

    // Ensure all robots have been withdrawn to the bench at this point
    if (!empty($this_player->player_robots)){
      foreach ($this_player->player_robots AS $temp_key => $temp_robotinfo){
        $temp_robot = new mmrpg_robot($this, $this_player, $temp_robotinfo);
        $temp_robot->robot_position = 'bench';
        $temp_robot->update_session();
      }
    }

    // Switch in the player's new robot and display an event for it
    $this_robot->robot_load($this_robotinfo);
    if ($this_robot->robot_position != 'active'){
      $this_robot->robot_position = 'active';
      $this_player->player_frame = 'command';
      $this_player->values['current_robot'] = $this_robot->robot_string;
      $this_player->values['current_robot_enter'] = $this->counters['battle_turn'];
      $this_robot->update_session();
      $this_player->update_session();
      $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
      $event_body = "{$this_robot->print_robot_name()} joins the battle!<br />";
      if (isset($this_robot->robot_quotes['battle_start'])){
        $this_robot->robot_frame = 'taunt';
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_robot->print_robot_quote('battle_start', $this_find, $this_replace);
      }
      // Only show the enter event if the switch reason was removed or if there is more then one robot
      if ($this_switch_reason == 'removed' || $this_player->counters['robots_active'] > 1){
        $this->events_create($this_robot, false, $event_header, $event_body);
      }
    }

    // Ensure this robot has abilities to loop through
    if (!isset($this_robot->flags['ability_startup']) && !empty($this_robot->robot_abilities)){
      // Loop through each of this robot's abilities and trigger the start event
      $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
      foreach ($this_robot->robot_abilities AS $this_key => $this_token){
        if (!isset($temp_abilities_index[$this_token])){ continue; }
        // Define the current ability object using the loaded ability data
        $temp_abilityinfo = mmrpg_ability::parse_index_info($temp_abilities_index[$this_token]);
        $temp_ability = new mmrpg_ability($this, $this_player, $this_robot, $temp_abilityinfo);
        // Update or create this abilities session object
        $temp_ability->update_session();
      }
      // And now update the robot with the flag
      $this_robot->flags['ability_startup'] = true;
      $this_robot->update_session();
    }

    // Now we can update the current robot's frame regardless of what happened
    $this_robot->robot_frame = $this_robot->robot_status != 'disabled' ? 'base' : 'defeat';
    $this_robot->update_session();

    // Set this token to the ID and token of the switched robot
    $this_token = $this_robotinfo['robot_id'].'_'.$this_robotinfo['robot_token'];

    //$this->events_create(false, false, 'DEBUG', 'checkpoint ['.$this_token.'] | other : []');

    // Return from the battle function
    $this_return = true;
    break;
  }
  // Else if the player has chosen to scan the target
  elseif ($this_action == 'scan'){
    // Otherwise, parse the token for data
    if (!empty($this_token)){
      list($temp_id, $temp_token) = explode('_', $this_token);
      $this_token = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
    }

    // If an ability token was not collected
    if (empty($this_token)){
      // Decide which robot should be scanned
      foreach ($target_player->player_robots AS $this_key => $this_robotinfo){
        if ($this_robotinfo['robot_position'] == 'active'){ $this_token = $this_robotinfo;  }
      }
    }

    // Create the temporary target player and robot objects
    $temp_target_robot_info = !empty($this->values['robots'][$this_token['robot_id']]) ? $this->values['robots'][$this_token['robot_id']] : array();
    $temp_target_player_info = !empty($this->values['players'][$temp_target_robot_info['player_id']]) ? $this->values['players'][$temp_target_robot_info['player_id']] : array();
    $temp_target_player = new mmrpg_player($this, $temp_target_player_info);
    $temp_target_robot = new mmrpg_robot($this, $temp_target_player, $temp_target_robot_info);

    // Ensure the target robot's frame is set to its base
    $temp_target_robot->robot_frame = 'base';
    $temp_target_robot->update_session();

    // Collect the weakness, resistsance, affinity, and immunity text
    $temp_target_robot_weaknesses = $temp_target_robot->print_robot_weaknesses();
    $temp_target_robot_resistances = $temp_target_robot->print_robot_resistances();
    $temp_target_robot_affinities = $temp_target_robot->print_robot_affinities();
    $temp_target_robot_immunities = $temp_target_robot->print_robot_immunities();

    // Collect the list of abilities for this robot
    $temp_target_robot_abilities = implode(', ', $temp_target_robot->robot_abilities);

    // Change the target robot's frame to defend base and save
    $temp_target_robot->robot_frame = 'taunt';
    $temp_target_robot->update_session();

    // Now change the target robot's frame is set to its mugshot
    $temp_target_robot->robot_frame = 'taunt'; //taunt';

    $temp_stat_padding_total = 300;
    $temp_stat_counter_total = $temp_target_robot->robot_energy + $temp_target_robot->robot_attack + $temp_target_robot->robot_defense + $temp_target_robot->robot_speed;
    $temp_stat_counter_base_total = $temp_target_robot->robot_base_energy + $temp_target_robot->robot_base_attack + $temp_target_robot->robot_base_defense + $temp_target_robot->robot_base_speed;

    $temp_energy_padding = ceil(($temp_target_robot->robot_energy / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_energy_base_padding = ceil(($temp_target_robot->robot_base_energy / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_energy_base_padding = $temp_energy_base_padding - $temp_energy_padding;

    $temp_attack_padding = ceil(($temp_target_robot->robot_attack / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_attack_base_padding = ceil(($temp_target_robot->robot_base_attack / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_attack_base_padding = $temp_attack_base_padding - $temp_attack_padding;
    if ($temp_attack_padding < 1){ $temp_attack_padding = 0; }
    if ($temp_attack_base_padding < 1){ $temp_attack_base_padding = 0; }

    $temp_defense_padding = ceil(($temp_target_robot->robot_defense / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_defense_base_padding = ceil(($temp_target_robot->robot_base_defense / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_defense_base_padding = $temp_defense_base_padding - $temp_defense_padding;
    if ($temp_defense_padding < 1){ $temp_defense_padding = 0; }
    if ($temp_defense_base_padding < 1){ $temp_defense_base_padding = 0; }

    $temp_speed_padding = ceil(($temp_target_robot->robot_speed / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_speed_base_padding = ceil(($temp_target_robot->robot_base_speed / $temp_stat_counter_base_total) * $temp_stat_padding_total);
    $temp_speed_base_padding = $temp_speed_base_padding - $temp_speed_padding;
    if ($temp_speed_padding < 1){ $temp_speed_padding = 0; }
    if ($temp_speed_base_padding < 1){ $temp_speed_base_padding = 0; }

    // Create an event showing the scanned robot's data
    $event_header = ($temp_target_player->player_token != 'player' ? $temp_target_player->player_name.'&#39;s ' : '').$temp_target_robot->robot_name;
    if (empty($_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned'])){ $event_header .= ' (New!)'; }
    $event_body = '';
    ob_start();
    ?>
        <table class="full">
          <colgroup>
            <col width="20%" />
            <col width="43%" />
            <col width="4%" />
            <col width="13%" />
            <col width="20%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="left">Name  : </td>
              <td  class="right"><?= !preg_match('/^dark-(frag|spire|tower)$/i', $temp_target_robot->robot_token) ? $temp_target_robot->print_robot_number() : '' ?> <?= $temp_target_robot->print_robot_name() ?></td>
              <td class="center">&nbsp;</td>
              <td class="left">Core : </td>
              <td  class="right"><?= $temp_target_robot->print_robot_core() ?></td>
            </tr>
            <tr>
              <td class="left">Weaknesses : </td>
              <td  class="right"><?= !empty($temp_target_robot_weaknesses) ? $temp_target_robot_weaknesses : '<span class="robot_weakness">None</span>' ?></td>
              <td class="center">&nbsp;</td>
              <td class="left">Energy : </td>
              <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_energy / $temp_target_robot->robot_base_energy) * 100).'% | '.$temp_target_robot->robot_energy.' / '.$temp_target_robot->robot_base_energy ?>"data-tooltip-type="robot_type robot_type_energy" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_energy_base_padding ?>px;"><span class="robot_stat robot_type robot_type_energy" style="padding-left: <?= $temp_energy_padding ?>px;"><?= $temp_target_robot->robot_energy ?></span></span></td>
            </tr>
            <tr>
              <td class="left">Resistances : </td>
              <td  class="right"><?= !empty($temp_target_robot_resistances) ? $temp_target_robot_resistances : '<span class="robot_resistance">None</span>' ?></td>
              <td class="center">&nbsp;</td>
              <td class="left">Attack : </td>
              <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_attack / $temp_target_robot->robot_base_attack) * 100).'% | '.$temp_target_robot->robot_attack.' / '.$temp_target_robot->robot_base_attack ?>"data-tooltip-type="robot_type robot_type_attack" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_attack_base_padding ?>px;"><span class="robot_stat robot_type robot_type_attack" style="padding-left: <?= $temp_attack_padding ?>px;"><?= $temp_target_robot->robot_attack ?></span></span></td>
            </tr>
            <tr>
              <td class="left">Affinities : </td>
              <td  class="right"><?= !empty($temp_target_robot_affinities) ? $temp_target_robot_affinities : '<span class="robot_affinity">None</span>' ?></td>
              <td class="center">&nbsp;</td>
              <td class="left">Defense : </td>
              <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_defense / $temp_target_robot->robot_base_defense) * 100).'% | '.$temp_target_robot->robot_defense.' / '.$temp_target_robot->robot_base_defense ?>"data-tooltip-type="robot_type robot_type_defense" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_defense_base_padding ?>px;"><span class="robot_stat robot_type robot_type_defense" style="padding-left: <?= $temp_defense_padding ?>px;"><?= $temp_target_robot->robot_defense ?></span></span></td>
            </tr>
            <tr>
              <td class="left">Immunities : </td>
              <td  class="right"><?= !empty($temp_target_robot_immunities) ? $temp_target_robot_immunities : '<span class="robot_immunity">None</span>' ?></td>
              <td class="center">&nbsp;</td>
              <td class="left">Speed : </td>
              <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_speed / $temp_target_robot->robot_base_speed) * 100).'% | '.$temp_target_robot->robot_speed.' / '.$temp_target_robot->robot_base_speed ?>"data-tooltip-type="robot_type robot_type_speed" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_speed_base_padding ?>px;"><span class="robot_stat robot_type robot_type_speed" style="padding-left: <?= $temp_speed_padding ?>px;"><?= $temp_target_robot->robot_speed ?></span></span></td>
            </tr>
            <?/*
            <tr>
              <td class="left" colspan="5">
                Abilities :
                <?= !empty($temp_target_robot_abilities) ? $temp_target_robot_abilities : '<span class="ability_name">None</span>' ?>
              </td>
            </tr>
            */?>
          </tbody>
        </table>
    <?
    $event_body .= preg_replace('#\s+#', ' ', trim(ob_get_clean()));
    $this->events_create($temp_target_robot, false, $event_header, $event_body, array('console_container_height' => 2, 'canvas_show_this' => false)); //, 'event_flag_autoplay' => false

    // Ensure the target robot's frame is set to its base
    $temp_target_robot->robot_frame = 'base';
    $temp_target_robot->update_session();

    // Add this robot to the global robot database array
    if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token])){ $_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token] = array('robot_token' => $temp_target_robot->robot_token); }
    if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned'])){ $_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned'] = 0; }
    $_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned']++;

    // Set this token to the ID and token of the triggered ability
    $this_token = $this_token['robot_id'].'_'.$this_token['robot_token'];

    // Return from the battle function with the scanned robot
    $this_return = &$this_ability;
    break;

  }

  // Break out of the battle loop by default
  break;
}

// Set the hidden flag on this robot if necessary
if ($this_robot->robot_position == 'bench' && ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1)){
  $this_robot->flags['apply_disabled_state'] = true;
  $this_robot->flags['hidden'] = true;
  $this_robot->update_session();
}

// Set the hidden flag on the target robot if necessary
if ($target_robot->robot_position == 'bench' && ($target_robot->robot_status == 'disabled' || $target_robot->robot_energy < 1)){
  $target_robot->flags['apply_disabled_state'] = true;
  $target_robot->flags['hidden'] = true;
  $target_robot->update_session();
}

// If the target player does not have any robots left
if ($target_player->counters['robots_active'] == 0){

  // Trigger the battle complete action to update status and result
  $this->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_token);

}

// Update this player's history object with this action
$this_player->history['actions'][] = array(
    'this_action' => $this_action,
    'this_action_token' => $this_token
    );

// Update this battle's session data
$this->update_session();

// Update this player's session data
$this_player->update_session();
// Update the target player's session data
$target_player->update_session();

// Update this robot's session data
$this_robot->update_session();
// Update the target robot's session data
$target_robot->update_session();

// Update the current ability's session data
if (isset($this_ability)){
  $this_ability->update_session();
}
?>