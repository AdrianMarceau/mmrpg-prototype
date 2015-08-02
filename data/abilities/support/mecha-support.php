<?
// MECHA SUPPORT
$ability = array(
  'ability_name' => 'Mecha Support',
  'ability_token' => 'mecha-support',
  'ability_group' => 'MMRPG/Support/Special',
  'ability_description' => 'The user summons a familiar support mecha from their own home field to their side of the battle, allowing it to temporarily fight as part of the user\'s own team! This ability seems to work differently for Neutral and Copy Core robots...',
  'ability_energy' => 8,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);
    $mmrpg_index_fields = mmrpg_field::get_index();

    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 0, 0, 10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

    /*
    // Create the entry message for this new mecha added to the field
    $this_robot->robot_frame = 'summon';
    $this_robot->update_session();
    $event_header = $this_robot->robot_name.'&#39;s '.$this_ability->ability_name;
    $event_body = $this_robot->print_robot_name().' uses the '.$this_ability->print_ability_name().'!';
    $this_battle->events_create($this_robot, false, $event_header, $event_body);
    */

    // Only continue with the ability if player has less than 8 robots
    if (count($this_player->player_robots) < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX){
      // Place the current robot back on the bench
      $this_original_robot_id = $this_robot->robot_id;
      $this_robot->robot_frame = 'taunt';
      $this_robot->robot_position = 'bench';
      $this_player->player_frame = 'base';
      $this_player->values['current_robot'] = false;
      $this_player->values['current_robot_enter'] = false;
      $this_robot->update_session();
      $this_player->update_session();

      // Collect the current robot level for this field
      $this_robot_level = !empty($this_robot->robot_level) ? $this_robot->robot_level : 1;
      $this_field_level = !empty($this_battle->battle_level) ? $this_battle->battle_level : 1;

      // Check if this robot is a Copy Core or Elemental Core (skip if Neutral)
      global $mmrpg_index;
      $this_field_mechas = array();
      if (!empty($this_robot->robot_core)){
        $special_boss_robots = array('enker', 'punk', 'ballade');
        if ($this_robot->robot_core == 'copy' || in_array($this_robot->robot_token, $special_boss_robots)){
          // Collect the current robots available for this current field
          $this_field_mechas = !empty($this_battle->battle_field->field_mechas) ? $this_battle->battle_field->field_mechas : array();
        } elseif (!empty($this_robot->robot_field)){
          // Collect the current robots available for this robot's home field
          // Collect the current mechas available for this robot's home field
          $temp_field = !empty($mmrpg_index_fields[$this_robot->robot_field]) ? $mmrpg_index_fields[$this_robot->robot_field] : array();
          $this_field_info = mmrpg_field::parse_index_info($temp_field);
          $this_field_mechas = !empty($this_field_info['field_mechas']) ? $this_field_info['field_mechas'] : array();
        }
      }

      // Remove any mechas that are of too high a level to unlock
      /*
      foreach ($this_field_mechas AS $temp_key => $temp_token){
        $temp_base_token = preg_replace('/-([0-9]+)$/i', '', $temp_token);
        $temp_unlock_token = false;
        if (preg_match('/-2$/i', $temp_token)){ $temp_unlock_token = $temp_base_token; }
        if (preg_match('/-3$/i', $temp_token)){ $temp_unlock_token = $temp_base_token.'-2'; }
        if (!empty($temp_unlock_token) && !empty($_SESSION['GAME']['values']['robot_database'][$temp_unlock_token])){ unset($this_field_mechas[$temp_key]); }
      }
      */

      // If no mechas were defined, default to the Met
      if (empty($this_field_mechas)){
        $this_field_mechas[] = 'met';
      }

      // Pull a random mecha element out of the array
      $this_mecha_count = count($this_field_mechas);
      $this_mecha_token = $this_field_mechas[0]; //$this_field_mechas[array_rand($this_field_mechas)];
      $this_mecha_name_token = preg_replace('/-([1-3]+)$/i', '', $this_mecha_token);
      if (empty($_SESSION['GAME']['values']['robot_database'][$this_mecha_token]['robot_summoned'])){ $_SESSION['GAME']['values']['robot_database'][$this_mecha_token]['robot_summoned'] = 0; }
      $this_mecha_summoned_counter = $_SESSION['GAME']['values']['robot_database'][$this_mecha_token]['robot_summoned'];

      // Check to see if this robot has summoned a mecha during this battle already
      if (!isset($this_robot->counters['ability_mecha_support'])){ $this_robot->counters['ability_mecha_support'] = 0; }
      $this_robot->update_session();

      // Based on the number of summons this battle, decide which in rotation to use
      $temp_summon_pos = $this_robot->counters['ability_mecha_support'] + 1;
      if ($this_mecha_count == 1){ $temp_summon_pos = 1; }
      elseif ($temp_summon_pos > $this_mecha_count){
        $temp_summon_pos = $temp_summon_pos % $this_mecha_count;
        if ($temp_summon_pos < 1){ $temp_summon_pos = $this_mecha_count; }
      }
      $temp_summon_key = $temp_summon_pos - 1;
      $this_mecha_token = $this_field_mechas[$temp_summon_key];

      // Update the summon flag now that we're done with it
      $this_robot->counters['ability_mecha_support'] += 1;
      $this_robot->update_session();

      // Collect database info for this mecha
      global $DB;
      $this_mecha_info = mmrpg_robot::get_index_info($this_mecha_token);
      $this_mecha_info = mmrpg_robot::parse_index_info($this_mecha_info);

      // Update or create the mecha letter token
      if (!isset($this_player->counters['player_mechas'][$this_mecha_name_token])){ $this_player->counters['player_mechas'][$this_mecha_name_token] = 0; }
      else { $this_player->counters['player_mechas'][$this_mecha_name_token]++; }
      $this_player->update_session();

      // Add this robot's token to the robot database, as to unlock this robot's ability data
      if (!isset($_SESSION['GAME']['values']['robot_database'][$this_mecha_token])){ $_SESSION['GAME']['values']['robot_database'][$this_mecha_token] = array('robot_token' => $this_mecha_token); }
      if (!isset($_SESSION['GAME']['values']['robot_database'][$this_mecha_token]['robot_summoned'])){ $_SESSION['GAME']['values']['robot_database'][$this_mecha_token]['robot_summoned'] = 0; }
      $_SESSION['GAME']['values']['robot_database'][$this_mecha_token]['robot_summoned'] += 1;
      $this_mecha_summoned_counter = $_SESSION['GAME']['values']['robot_database'][$this_mecha_token]['robot_summoned'];

      // Decide which letter to attach to this mecha
      $this_letter_options = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
      $this_mecha_letter = $this_letter_options[$this_player->counters['player_mechas'][$this_mecha_name_token]];

      // DEBUG
      // Generate the new robot and add it to this player's team
      $this_key = $this_player->counters['robots_active'] + $this_player->counters['robots_disabled'];
      $this_id = $this_player->player_id + 2 + $this_key;
      $this_id_token = $this_id.'_'.$this_mecha_info['robot_token'];
      $this_mecha_info['robot_id'] = $this_id;
      $this_mecha_info['robot_key'] = $this_key;
      $this_mecha_info['robot_position'] = 'active';
      $this_mecha_info['robot_experience'] = 0;
      $this_mecha_info['robot_level'] = $this_mecha_summoned_counter >= 100 ? 100 : $this_mecha_summoned_counter;
      //if (preg_match('/-2$/', $this_mecha_token)){ $this_mecha_info['robot_name'] .= '²'; }
      //elseif (preg_match('/-3$/', $this_mecha_token)){ $this_mecha_info['robot_name'] .= '³'; }
      $this_mecha_info['robot_name'] .= ' '.$this_mecha_letter;
      $temp_mecha = new mmrpg_robot($this_battle, $this_player, $this_mecha_info);
      $temp_mecha->apply_stat_bonuses();
      foreach ($temp_mecha->robot_abilities AS $this_key2 => $this_token){
        $temp_abilityinfo = array('ability_token' => $this_token);
        $temp_ability = new mmrpg_ability($this_battle, $this_player, $temp_mecha, $temp_abilityinfo);
        $temp_ability->update_session();
      }
      $temp_mecha->flags['ability_startup'] = true;
      $temp_mecha->update_session();
      $this_mecha_info = $temp_mecha->export_array();
      $this_player->load_robot($this_mecha_info, $this_key);
      $this_player->update_session();

      // Automatically trigger a switch action to the new mecha support robot
      $this_battle->actions_trigger($this_player, $this_robot, $target_player, $target_robot, 'switch', $this_id_token);

      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG '.__LINE__, 'this_mecha_token = '.$this_mecha_token);

      // Refresh the current robot's frame back to normal (manually because reference confusion)
      mmrpg_robot::set_session_field($this_original_robot_id, 'robot_frame', 'base');

    }
    // Otherwise print a nothing happened message
    else {

      // Update the ability's target options and trigger
      $this_ability->target_options_update(array(
        'frame' => 'defend',
        'success' => array(0, 0, 0, 10, '&hellip;but nothing happened.')
        ));
      $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

    }

    // Return true on success
    return true;

    }
  );
?>