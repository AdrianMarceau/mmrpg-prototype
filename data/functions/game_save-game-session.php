<?
// Define a function for saving the game session
function mmrpg_save_game_session($this_save_filepath){
  // Reference global variables
  global $DB;
  $session_token = mmrpg_game_token();
  $mmrpg_index_players = &$GLOBALS['mmrpg_index']['players'];

  // Do NOT load, save, or otherwise alter the game file while viewing remote
  if (defined('MMRPG_REMOTE_GAME')){ return true; }
  // Update the last saved value
  $_SESSION[$session_token]['values']['last_save'] = time();

  // If this is NOT demo mode, load from database
  if (empty($_SESSION[$session_token]['DEMO'])){
    // UPDATE DATABASE INFO

    // Collect the save info
    $save = $_SESSION[$session_token];
    $this_user = $save['USER'];
    $this_file = $save['FILE'];
    $this_cache_date = !empty($save['CACHE_DATE']) ? $save['CACHE_DATE'] : MMRPG_CONFIG_CACHE_DATE;
    $this_counters = !empty($save['counters']) ? $save['counters'] : array();
    $this_values = !empty($save['values']) ? $save['values'] : array();
    $this_flags = !empty($save['flags']) ? $save['flags'] : array();
    $this_settings = !empty($save['battle_settings']) ? $save['battle_settings'] : array();
    $this_stars = !empty($save['values']['battle_stars']) ? $save['values']['battle_stars'] : array();
    unset($save);
    // Collect this user's ID from the database if not set
    if (!isset($this_user['userid'])){
      // Attempt to collect the user ID from the database
      $temp_query = "SELECT user_id FROM mmrpg_users WHERE user_name_clean = '{$this_user['username_clean']}' LIMIT 1";
      $temp_value = $DB->get_value($temp_query, 'user_id');
      // If the user ID was found, collect it and proceed as normal
      if (!empty($temp_value)){
        // Update the ID in the user array and continue
        $this_user['userid'] = $temp_value;

      }
      // Otherwise, create database rows for this new file
      else {
        // Generate new user, save, and board IDs for this listing
        $temp_user_id = $DB->get_value('SELECT MAX(user_id) AS user_id FROM mmrpg_users WHERE user_id < '.MMRPG_SETTINGS_GUEST_ID, 'user_id') + 1;
        $temp_save_id = $DB->get_value('SELECT MAX(save_id) AS save_id FROM mmrpg_saves', 'save_id') + 1;
        $temp_board_id = $DB->get_value('SELECT MAX(board_id) AS board_id FROM mmrpg_leaderboard', 'board_id') + 1;
        // Generate the USER details for import
        $temp_user_array = array();
        $temp_user_array['user_id'] = $temp_user_id;
        $temp_user_array['role_id'] = isset($this_user['roleid']) ? $this_user['roleid'] : 3;
        $temp_user_array['user_name'] = $this_user['username'];
        $temp_user_array['user_name_clean'] = $this_user['username_clean'];
        $temp_user_array['user_name_public'] = !empty($this_user['displayname']) ? $this_user['displayname'] : '';
        $temp_user_array['user_password'] = $this_user['password'];
        $temp_user_array['user_password_encoded'] = $this_user['password_encoded'];
        $temp_user_array['user_profile_text'] = !empty($this_user['profiletext']) ? $this_user['profiletext'] : '';
        $temp_user_array['user_credit_text'] = !empty($this_user['creditstext']) ? $this_user['creditstext'] : '';
        $temp_user_array['user_credit_line'] = !empty($this_user['creditsline']) ? $this_user['creditsline'] : '';
        $temp_user_array['user_image_path'] = !empty($this_user['imagepath']) ? $this_user['imagepath'] : '';
        $temp_user_array['user_background_path'] = !empty($this_user['backgroundpath']) ? $this_user['backgroundpath'] : '';
        $temp_user_array['user_colour_token'] = !empty($this_user['colourtoken']) ? $this_user['colourtoken'] : '';
        $temp_user_array['user_gender'] = !empty($this_user['gender']) ? $this_user['gender'] : '';
        $temp_user_array['user_email_address'] = !empty($this_user['emailaddress']) ? $this_user['emailaddress'] : '';
        $temp_user_array['user_website_address'] = !empty($this_user['websiteaddress']) ? $this_user['websiteaddress'] : '';
        $temp_user_array['user_date_created'] = time();
        $temp_user_array['user_date_accessed'] = time();
        $temp_user_array['user_date_modified'] = time();
        $temp_user_array['user_date_birth'] = !empty($this_user['dateofbirth']) ? $this_user['dateofbirth'] : 0;
        $temp_user_array['user_flag_approved'] = !empty($this_user['approved']) ? 1 : 0;

        // Generate the BOARD details for import
        $temp_board_array = array();
        $temp_board_array['board_id'] = $temp_board_id;
        $temp_board_array['user_id'] = $temp_user_id;
        $temp_board_array['save_id'] = $temp_save_id;
        $temp_board_array['board_points'] = !empty($this_counters['battle_points']) ? $this_counters['battle_points'] : 0;
        $temp_board_array['board_robots'] = array();
        $temp_board_array['board_battles'] = array();
        $temp_board_array['board_stars'] = 0;
        $temp_board_array['board_stars_dr_light'] = 0;
        $temp_board_array['board_stars_dr_wily'] = 0;
        $temp_board_array['board_stars_dr_cossack'] = 0;
        $temp_board_array['board_abilities'] = 0;
        $temp_board_array['board_abilities_dr_light'] = 0;
        $temp_board_array['board_abilities_dr_wily'] = 0;
        $temp_board_array['board_abilities_dr_cossack'] = 0;
        $temp_board_array['board_missions'] = 0;
        $temp_board_array['board_missions_dr_light'] = 0;
        $temp_board_array['board_missions_dr_wily'] = 0;
        $temp_board_array['board_missions_dr_cossack'] = 0;
        $temp_board_ability_tokens = array();
        if (!empty($this_values['battle_rewards'])){
          //foreach ($this_values['battle_rewards'] AS $player_token => $player_array){
          foreach ($mmrpg_index_players AS $player_token => $player_array){
            if ($player_token == 'player'){ continue; }
            $player_reward_array = !empty($this_values['battle_rewards'][$player_token]) ? $this_values['battle_rewards'][$player_token] : array();
            $player_battles_array = !empty($this_values['battle_complete'][$player_token]) ? $this_values['battle_complete'][$player_token] : array();
            $player_database_token = str_replace('-', '_', $player_token);
            if (!empty($player_reward_array)){
              $temp_board_array['board_points_'.$player_database_token] = $player_reward_array['player_points'];
              $temp_board_array['board_robots_'.$player_database_token] = array();
              $temp_board_array['board_battles_'.$player_database_token] = array();
              if (!empty($player_reward_array['player_robots'])){
                foreach ($player_reward_array['player_robots'] AS $robot_token => $robot_array){
                  $temp_token = $robot_array['robot_token'];
                  $temp_level = !empty($robot_array['robot_level']) ? $robot_array['robot_level'] : 1;
                  $temp_robot_info = array('robot_token' => $temp_token, $temp_level);
                  $temp_board_array['board_robots'][] = '['.$temp_token.':'.$temp_level.']';
                  $temp_board_array['board_robots_'.$player_database_token][] = '['.$temp_token.':'.$temp_level.']';
                }
              }
              if (!empty($player_reward_array['player_abilities'])){
                foreach ($player_reward_array['player_abilities'] AS $ability_token => $ability_array){
                  //if (!isset($ability_array['ability_token'])){ die('player_abilities->'.print_r($ability_array, true)); }
                  $temp_token = !empty($ability_array['ability_token']) ? $ability_array['ability_token']: $ability_token;
                  $temp_board_array['board_abilities_'.$player_database_token] += 1;
                  if (!in_array($temp_token, $temp_board_ability_tokens)){
                    $temp_board_array['board_abilities'] += 1;
                    $temp_board_ability_tokens[] = $temp_token;
                  }
                }
              }
              if (!empty($player_battles_array)){
                foreach ($player_battles_array AS $battle_token => $battle_info){
                  $temp_token = $battle_info['battle_token'];
                  $temp_board_array['board_battles'][] = '['.$temp_token.']';
                  $temp_board_array['board_battles_'.$player_database_token][] = '['.$temp_token.']';
                  $temp_board_array['board_missions'] += 1;
                  $temp_board_array['board_missions_'.$player_database_token] += 1;
                }
              }
            } else {
              $temp_board_array['board_points_'.$player_database_token] = 0;
              $temp_board_array['board_robots_'.$player_database_token] = array();
              $temp_board_array['board_battles_'.$player_database_token] = array();
            }
            $temp_board_array['board_robots_'.$player_database_token] = !empty($temp_board_array['board_robots_'.$player_database_token]) ? implode(',', $temp_board_array['board_robots_'.$player_database_token]) : '';
            $temp_board_array['board_battles_'.$player_database_token] = !empty($temp_board_array['board_battles_'.$player_database_token]) ? implode(',', $temp_board_array['board_battles_'.$player_database_token]) : '';
          }
        }

        if (!empty($this_stars)){
          foreach ($this_stars AS $temp_star_token => $temp_star_info){
            $temp_star_player = str_replace('-', '_', $temp_star_info['star_player']);
            $temp_board_array['board_stars'] += 1;
            $temp_board_array['board_stars_'.$temp_star_player] += 1;
          }
        }

        $temp_board_array['board_robots'] = !empty($temp_board_array['board_robots']) ? implode(',', $temp_board_array['board_robots']) : '';
        $temp_board_array['board_battles'] = !empty($temp_board_array['board_battles']) ? implode(',', $temp_board_array['board_battles']) : '';
        $temp_board_array['board_date_created'] = $temp_user_array['user_date_created'];
        $temp_board_array['board_date_modified'] = $temp_user_array['user_date_modified'];

        // Generate the SAVE details for import
        $temp_save_array = array();
        if (!empty($this_values['battle_index'])){
          unset($this_values['battle_index']);
          }
        if (!empty($this_values['battle_complete'])){
          $temp_save_array['save_values_battle_complete'] = json_encode($this_values['battle_complete']);
          $temp_hash = md5($temp_save_array['save_values_battle_complete']);
          if (isset($this_values['battle_complete_hash']) && $this_values['battle_complete_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_complete']); }
          unset($this_values['battle_complete'], $this_values['battle_complete_hash']);
          }
        if (!empty($this_values['battle_failure'])){
          $temp_save_array['save_values_battle_failure'] = json_encode($this_values['battle_failure']);
          $temp_hash = md5($temp_save_array['save_values_battle_failure']);
          if (isset($this_values['battle_failure_hash']) && $this_values['battle_failure_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_failure']); }
          unset($this_values['battle_failure'], $this_values['battle_failure_hash']);
          }
        if (!empty($this_values['battle_rewards'])){
          $temp_save_array['save_values_battle_rewards'] = json_encode($this_values['battle_rewards']);
          $temp_hash = md5($temp_save_array['save_values_battle_rewards']);
          if (isset($this_values['battle_rewards_hash']) && $this_values['battle_rewards_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_rewards']); }
          unset($this_values['battle_rewards'], $this_values['battle_rewards_hash']);
          }
        if (!empty($this_values['battle_settings'])){
          $temp_save_array['save_values_battle_settings'] = json_encode($this_values['battle_settings']);
          $temp_hash = md5($temp_save_array['save_values_battle_settings']);
          if (isset($this_values['battle_settings_hash']) && $this_values['battle_settings_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_settings']); }
          unset($this_values['battle_settings'], $this_values['battle_settings_hash']);
          }
        if (!empty($this_values['battle_items'])){
          $temp_save_array['save_values_battle_items'] = json_encode($this_values['battle_items']);
          $temp_hash = md5($temp_save_array['save_values_battle_items']);
          if (isset($this_values['battle_items_hash']) && $this_values['battle_items_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_items']); }
          unset($this_values['battle_items'], $this_values['battle_items_hash']);
          }
        if (!empty($this_values['battle_stars'])){
          $temp_save_array['save_values_battle_stars'] = json_encode($this_values['battle_stars']);
          $temp_hash = md5($temp_save_array['save_values_battle_stars']);
          if (isset($this_values['battle_stars_hash']) && $this_values['battle_stars_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_stars']); }
          unset($this_values['battle_stars'], $this_values['battle_stars_hash']);
          }
        if (!empty($this_values['robot_database'])){
          $temp_save_array['save_values_robot_database'] = json_encode($this_values['robot_database']);
          $temp_hash = md5($temp_save_array['save_values_robot_database']);
          if (isset($this_values['robot_database_hash']) && $this_values['robot_database_hash'] == $temp_hash){ unset($temp_save_array['save_values_robot_database']); }
          unset($this_values['robot_database'], $this_values['robot_database_hash']);
          }
        $temp_save_array['save_id'] = $temp_save_id;
        $temp_save_array['user_id'] = $temp_user_id;
        $temp_save_array['save_counters'] = json_encode($this_counters);
        $temp_save_array['save_values'] = json_encode($this_values);
        $temp_save_array['save_flags'] = json_encode($this_flags);
        $temp_save_array['save_settings'] = json_encode($this_settings);
        $temp_save_array['save_cache_date'] = $this_cache_date;
        $temp_save_array['save_file_name'] = $this_file['name'];
        $temp_save_array['save_file_path'] = $this_file['path'];
        $temp_save_array['save_date_created'] = $temp_user_array['user_date_created'];
        $temp_save_array['save_date_accessed'] = $temp_user_array['user_date_accessed'];
        $temp_save_array['save_date_modified'] = $temp_user_array['user_date_modified'];

        // Insert these users into the database
        $temp_user_array_return = $DB->insert('mmrpg_users', $temp_user_array);
        $temp_save_array_return = $DB->insert('mmrpg_saves', $temp_save_array);
        $temp_board_array_return = $DB->insert('mmrpg_leaderboard', $temp_board_array);
        unset($temp_user_array, $temp_save_array, $temp_board_array);
        // Update the ID in the user array and continue
        $this_user['userid'] = $temp_user_id;

      }
    }

    // DEBUG
    $DEBUG = '';

    // Define the user database update array and populate
    $temp_user_array = array();
    $temp_user_array['user_name'] = $this_user['username'];
    $temp_user_array['user_name_clean'] = $this_user['username_clean'];
    $temp_user_array['user_name_public'] = !empty($this_user['displayname']) ? $this_user['displayname'] : '';
    $temp_user_array['user_password'] = $this_user['password'];
    $temp_user_array['user_password_encoded'] = $this_user['password_encoded'];
    $temp_user_array['user_profile_text'] = !empty($this_user['profiletext']) ? $this_user['profiletext'] : '';
    $temp_user_array['user_credit_text'] = !empty($this_user['creditstext']) ? $this_user['creditstext'] : '';
    $temp_user_array['user_credit_line'] = !empty($this_user['creditsline']) ? $this_user['creditsline'] : '';
    $temp_user_array['user_image_path'] = !empty($this_user['imagepath']) ? $this_user['imagepath'] : '';
    $temp_user_array['user_background_path'] = !empty($this_user['backgroundpath']) ? $this_user['backgroundpath'] : '';
    $temp_user_array['user_colour_token'] = !empty($this_user['colourtoken']) ? $this_user['colourtoken'] : '';
    $temp_user_array['user_gender'] = !empty($this_user['gender']) ? $this_user['gender'] : '';
    $temp_user_array['user_email_address'] = !empty($this_user['emailaddress']) ? $this_user['emailaddress'] : '';
    $temp_user_array['user_website_address'] = !empty($this_user['websiteaddress']) ? $this_user['websiteaddress'] : '';
    $temp_user_array['user_date_modified'] = time();
    $temp_user_array['user_date_accessed'] = time();
    $temp_user_array['user_date_birth'] = !empty($this_user['dateofbirth']) ? $this_user['dateofbirth'] : 0;
    $temp_user_array['user_flag_approved'] = !empty($this_user['approved']) ? 1 : 0;
    // Update this user's info in the database
    $DB->update('mmrpg_users', $temp_user_array, 'user_id = '.$this_user['userid']);
    unset($temp_user_array);
    // DEBUG
    //$DEBUG .= '$DB->update(\'mmrpg_users\', $temp_user_array, \'user_id = \'.$this_user[\'userid\']);';
    //$DEBUG .= '<pre>$temp_user_array = '.print_r($temp_user_array, true).'</pre>';
    //$DEBUG .= '<pre>$this_user = '.print_r($this_user, true).'</pre>';

    // Define the board database update array and populate
    $temp_board_array = array();
    $temp_board_array['board_points'] = !empty($this_counters['battle_points']) ? $this_counters['battle_points'] : 0;
    $temp_board_array['board_robots'] = array();
    $temp_board_array['board_battles'] = array();
    $temp_board_array['board_stars'] = 0;
    $temp_board_array['board_stars_dr_light'] = 0;
    $temp_board_array['board_stars_dr_wily'] = 0;
    $temp_board_array['board_stars_dr_cossack'] = 0;
    $temp_board_array['board_abilities'] = 0;
    $temp_board_array['board_abilities_dr_light'] = 0;
    $temp_board_array['board_abilities_dr_wily'] = 0;
    $temp_board_array['board_abilities_dr_cossack'] = 0;
    $temp_board_array['board_missions'] = 0;
    $temp_board_array['board_missions_dr_light'] = 0;
    $temp_board_array['board_missions_dr_wily'] = 0;
    $temp_board_array['board_missions_dr_cossack'] = 0;
    $temp_board_array['board_awards'] = !empty($this_values['prototype_awards']) ? array_keys($this_values['prototype_awards']) : '';

    $temp_board_ability_tokens = array();
    if (!empty($this_values['battle_rewards'])){
      //foreach ($this_values['battle_rewards'] AS $player_token => $player_array){
      foreach ($mmrpg_index_players AS $player_token => $player_array){
        if ($player_token == 'player' || !mmrpg_prototype_player_unlocked($player_token)){ continue; }
        $player_reward_array = !empty($this_values['battle_rewards'][$player_token]) ? $this_values['battle_rewards'][$player_token] : array();
        $player_battles_array = !empty($this_values['battle_complete'][$player_token]) ? $this_values['battle_complete'][$player_token] : array();
        $player_database_token = str_replace('-', '_', $player_token);
        if (!empty($player_reward_array)){
          $temp_board_array['board_points_'.$player_database_token] = !empty($player_reward_array['player_points']) ? $player_reward_array['player_points'] : 0;
          $temp_board_array['board_robots_'.$player_database_token] = array();
          $temp_board_array['board_battles_'.$player_database_token] = array();
          if (!empty($player_reward_array['player_robots'])){
            foreach ($player_reward_array['player_robots'] AS $robot_token => $robot_array){
              //if (!isset($robot_array['robot_token'])){ die('player_robots->'.print_r($robot_array, true)); }
              $temp_token = !empty($robot_array['robot_token']) ? $robot_array['robot_token']: $robot_token;
              $temp_level = !empty($robot_array['robot_level']) ? $robot_array['robot_level'] : 1;
              $temp_robot_info = array('robot_token' => $temp_token, $temp_level);
              $temp_board_array['board_robots'][] = '['.$temp_token.':'.$temp_level.']';
              $temp_board_array['board_robots_'.$player_database_token][] = '['.$temp_token.':'.$temp_level.']';
            }
          }
          if (!empty($player_reward_array['player_abilities'])){
            foreach ($player_reward_array['player_abilities'] AS $ability_token => $ability_array){
              //if (!isset($ability_array['ability_token'])){ die('player_abilities->'.print_r($ability_array, true)); }
              $temp_token = !empty($ability_array['ability_token']) ? $ability_array['ability_token']: $ability_token;
              $temp_board_array['board_abilities_'.$player_database_token] += 1;
              if (!in_array($temp_token, $temp_board_ability_tokens)){
                $temp_board_array['board_abilities'] += 1;
                $temp_board_ability_tokens[] = $temp_token;
              }
            }
          }
          if (!empty($player_battles_array)){
            foreach ($player_battles_array AS $battle_token => $battle_info){
              $temp_token = $battle_info['battle_token'];
              $temp_board_array['board_battles'][] = '['.$temp_token.']';
              $temp_board_array['board_battles_'.$player_database_token][] = '['.$temp_token.']';
              $temp_board_array['board_missions'] += 1;
              $temp_board_array['board_missions_'.$player_database_token] += 1;
            }
          }
        } else {
          $temp_board_array['board_points_'.$player_database_token] = 0;
          $temp_board_array['board_robots_'.$player_database_token] = array();
          $temp_board_array['board_battles_'.$player_database_token] = array();
        }
        $temp_board_array['board_robots_'.$player_database_token] = !empty($temp_board_array['board_robots_'.$player_database_token]) ? implode(',', $temp_board_array['board_robots_'.$player_database_token]) : '';
        $temp_board_array['board_battles_'.$player_database_token] = !empty($temp_board_array['board_battles_'.$player_database_token]) ? implode(',', $temp_board_array['board_battles_'.$player_database_token]) : '';
      }
    }

    if (!empty($this_stars)){
      foreach ($this_stars AS $temp_star_token => $temp_star_info){
        $temp_star_player = str_replace('-', '_', $temp_star_info['star_player']);
        $temp_board_array['board_stars'] += 1;
        $temp_board_array['board_stars_'.$temp_star_player] += 1;
      }
    }

    //$temp_board_array['board_robots'] = json_encode($temp_board_array['board_robots']);
    $temp_board_array['board_robots'] = !empty($temp_board_array['board_robots']) ? implode(',', $temp_board_array['board_robots']) : '';
    $temp_board_array['board_battles'] = !empty($temp_board_array['board_battles']) ? implode(',', $temp_board_array['board_battles']) : '';
    $temp_board_array['board_awards'] = !empty($temp_board_array['board_awards']) ? implode(',', $temp_board_array['board_awards']) : '';
    $temp_board_array['board_date_modified'] = time();

    // Update this board's info in the database
    $DB->update('mmrpg_leaderboard', $temp_board_array, 'user_id = '.$this_user['userid']);
    unset($temp_board_array);

    // Clear any leaderboard data that exists in the session, forcing it to recache
    if (isset($_SESSION[$session_token]['BOARD']['boardrank'])){ unset($_SESSION[$session_token]['BOARD']['boardrank']); }

    // Define the save database update array and populate
    $temp_save_array = array();
    if (!empty($this_values['battle_index'])){
      ////foreach ($this_values['battle_index'] AS $key => $array){ $this_values['battle_index'][$key] = json_decode($array, true); }
      ////$temp_save_array['save_values_battle_index'] = json_encode($this_values['battle_index']);
      ////$temp_hash = md5($temp_save_array['save_values_battle_index']);
      ////if (isset($this_values['battle_index_hash']) && $this_values['battle_index_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_index']); }
      //unset($this_values['battle_index'], $this_values['battle_index_hash']);
      unset($this_values['battle_index']);
      }
    if (!empty($this_values['battle_complete'])){
      $temp_save_array['save_values_battle_complete'] = json_encode($this_values['battle_complete']);
      $temp_hash = md5($temp_save_array['save_values_battle_complete']);
      if (isset($this_values['battle_complete_hash']) && $this_values['battle_complete_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_complete']); }
      unset($this_values['battle_complete'], $this_values['battle_complete_hash']);
      }
    if (!empty($this_values['battle_failure'])){
      $temp_save_array['save_values_battle_failure'] = json_encode($this_values['battle_failure']);
      $temp_hash = md5($temp_save_array['save_values_battle_failure']);
      if (isset($this_values['battle_failure_hash']) && $this_values['battle_failure_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_failure']); }
      unset($this_values['battle_failure'], $this_values['battle_failure_hash']);
      }
    if (!empty($this_values['battle_rewards'])){
      $temp_save_array['save_values_battle_rewards'] = json_encode($this_values['battle_rewards']);
      $temp_hash = md5($temp_save_array['save_values_battle_rewards']);
      if (isset($this_values['battle_rewards_hash']) && $this_values['battle_rewards_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_rewards']); }
      unset($this_values['battle_rewards'], $this_values['battle_rewards_hash']);
      }
    if (!empty($this_values['battle_settings'])){
      $temp_save_array['save_values_battle_settings'] = json_encode($this_values['battle_settings']);
      $temp_hash = md5($temp_save_array['save_values_battle_settings']);
      if (isset($this_values['battle_settings_hash']) && $this_values['battle_settings_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_settings']); }
      unset($this_values['battle_settings'], $this_values['battle_settings_hash']);
      }
    if (!empty($this_values['battle_items'])){
      $temp_save_array['save_values_battle_items'] = json_encode($this_values['battle_items']);
      $temp_hash = md5($temp_save_array['save_values_battle_items']);
      if (isset($this_values['battle_items_hash']) && $this_values['battle_items_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_items']); }
      unset($this_values['battle_items'], $this_values['battle_items_hash']);
      }
    if (!empty($this_values['battle_stars'])){
      $temp_save_array['save_values_battle_stars'] = json_encode($this_values['battle_stars']);
      $temp_hash = md5($temp_save_array['save_values_battle_stars']);
      if (isset($this_values['battle_stars_hash']) && $this_values['battle_stars_hash'] == $temp_hash){ unset($temp_save_array['save_values_battle_stars']); }
      unset($this_values['battle_stars'], $this_values['battle_stars_hash']);
      }
    if (!empty($this_values['robot_database'])){
      $temp_save_array['save_values_robot_database'] = json_encode($this_values['robot_database']);
      $temp_hash = md5($temp_save_array['save_values_robot_database']);
      if (isset($this_values['robot_database_hash']) && $this_values['robot_database_hash'] == $temp_hash){ unset($temp_save_array['save_values_robot_database']); }
      unset($this_values['robot_database'], $this_values['robot_database_hash']);
      }
    $temp_save_array['save_counters'] = json_encode($this_counters);
    $temp_save_array['save_values'] = json_encode($this_values);
    $temp_save_array['save_flags'] = json_encode($this_flags);
    $temp_save_array['save_settings'] = json_encode($this_settings);
    $temp_save_array['save_cache_date'] = $this_cache_date;
    $temp_save_array['save_file_name'] = $this_file['name'];
    $temp_save_array['save_file_path'] = $this_file['path'];
    $temp_save_array['save_date_modified'] = time();
    // Update this save's info in the database
    $DB->update('mmrpg_saves', $temp_save_array, 'user_id = '.$this_user['userid']);
    unset($temp_save_array);
    // DEBUG
    //$DEBUG .= '$DB->update(\'mmrpg_saves\', $temp_save_array, \'user_id = \'.$this_user[\'userid\']);';
    //$DEBUG .= '<pre>$temp_save_array = '.print_r($temp_save_array, true).'</pre>';
    //$DEBUG .= '<pre>$this_user = '.print_r($this_user, true).'</pre>';

    // DEBUG
    //$DEBUG .= '$DB->update(\'mmrpg_leaderboard\', $temp_board_array, \'user_id = \'.$this_user[\'userid\']);';
    //$DEBUG .= '<pre>$temp_board_array = '.print_r($temp_board_array, true).'</pre>';
    //$DEBUG .= '<pre>$this_user = '.print_r($this_user, true).'</pre>';

  } else {
    // DEBUG
    //echo 'but we\'re in demo mode';

  }

  // UPDATE SAVE FILE
  // Always update the save file so we have a backup
  // in case of future bugs with the system

  // Pull the base directory from this request
  $this_base_dir = preg_replace('#^(.*/)([a-z0-9]+\.sav)$#i', '$1', $this_save_filepath);
  if (!file_exists($this_base_dir)){ @mkdir($this_base_dir); }
  // Generate the save data by serializing the session variable
  $temp_game_session = $_SESSION[$session_token];
  $this_save_content = json_encode($temp_game_session);
  unset($temp_game_session);
  // Write the index to a cache file, if caching is enabled
  $this_save_file = fopen($this_save_filepath, 'w');
  fwrite($this_save_file, $this_save_content);
  fclose($this_save_file);

  // Return true on success
  return true;
}
?>