<?
// Define a function for loading the game session
function mmrpg_load_game_session($this_save_filepath){
  // Reference global variables
  global $DB;
  //$GAME_SESSION = &$_SESSION[mmrpg_game_token()];
  $session_token = mmrpg_game_token();

  // Do NOT load, save, or otherwise alter the game file while viewing remote
  if (defined('MMRPG_REMOTE_GAME')){ return true; }

  // Clear the community thread tracker
  $_SESSION['COMMUNITY']['threads_viewed'] = array();

  // If this is NOT demo mode, load from database
  if (empty($_SESSION[$session_token]['DEMO'])){

    // LOAD DATABASE INFO

    // Collect the user and save info from the database
    //$this_save_filepath = $this_save_dir.$this_file['path'].$this_file['name'];
    $temp_matches = array();
    preg_match('#/([-_a-z0-9]+/)([-_a-z0-9]+.sav)$#i', $this_save_filepath, $temp_matches);
    $this_database_save = $DB->get_array("SELECT * FROM mmrpg_saves WHERE save_file_name = '{$temp_matches[2]}' AND save_file_path = '{$temp_matches[1]}' LIMIT 1");
    $this_database_user =   $DB->get_array("SELECT * FROM mmrpg_users WHERE user_id = '{$this_database_save['user_id']}' LIMIT 1");

    // Update the game session with database extracted variables
    $new_game_data = array();

    $new_game_data['CACHE_DATE'] = $this_database_save['save_cache_date'];

    $new_game_data['USER']['userid'] = $this_database_user['user_id'];
    $new_game_data['USER']['roleid'] = $this_database_user['role_id'];
    $new_game_data['USER']['username'] = $this_database_user['user_name'];
    $new_game_data['USER']['username_clean'] = $this_database_user['user_name_clean'];
    $new_game_data['USER']['password'] = $this_database_user['user_password'];
    $new_game_data['USER']['password_encoded'] = $this_database_user['user_password_encoded'];
    $new_game_data['USER']['profiletext'] = $this_database_user['user_profile_text'];
    $new_game_data['USER']['creditstext'] = $this_database_user['user_credit_text'];
    $new_game_data['USER']['creditsline'] = $this_database_user['user_credit_line'];
    $new_game_data['USER']['imagepath'] = $this_database_user['user_image_path'];
    $new_game_data['USER']['backgroundpath'] = $this_database_user['user_background_path'];
    $new_game_data['USER']['colourtoken'] = $this_database_user['user_colour_token'];
    $new_game_data['USER']['gender'] = $this_database_user['user_gender'];
    $new_game_data['USER']['displayname'] = $this_database_user['user_name_public'];
    $new_game_data['USER']['emailaddress'] = $this_database_user['user_email_address'];
    $new_game_data['USER']['websiteaddress'] = $this_database_user['user_website_address'];
    $new_game_data['USER']['dateofbirth'] = $this_database_user['user_date_birth'];
    $new_game_data['USER']['approved'] = $this_database_user['user_flag_approved'];

    $new_game_data['FILE']['path'] = $this_database_save['save_file_path'];
    $new_game_data['FILE']['name'] = $this_database_save['save_file_name'];

    $new_game_data['counters'] = !empty($this_database_save['save_counters']) ? json_decode($this_database_save['save_counters'], true) : array();
    $new_game_data['values'] = !empty($this_database_save['save_values']) ? json_decode($this_database_save['save_values'], true) : array();
    if (!empty($this_database_save['save_values_battle_index'])){
      //$new_game_data['values']['battle_index'] = json_decode($this_database_save['save_values_battle_index'], true);
      //foreach ($new_game_data['values']['battle_index'] AS $token => $array){ $new_game_data['values']['battle_index'][$token] = json_encode($array); }
      //$new_game_data['values']['battle_index_hash'] = md5($this_database_save['save_values_battle_index']);
      $new_game_data['values']['battle_index'] = array();
    }
    if (!empty($this_database_save['save_values_battle_complete'])){
      $new_game_data['values']['battle_complete'] = json_decode($this_database_save['save_values_battle_complete'], true);
      $new_game_data['values']['battle_complete_hash'] = md5($this_database_save['save_values_battle_complete']);
    }
    if (!empty($this_database_save['save_values_battle_failure'])){
      $new_game_data['values']['battle_failure'] = json_decode($this_database_save['save_values_battle_failure'], true);
      $new_game_data['values']['battle_failure_hash'] = md5($this_database_save['save_values_battle_failure']);
    }
    if (!empty($this_database_save['save_values_battle_rewards'])){
      $new_game_data['values']['battle_rewards'] = json_decode($this_database_save['save_values_battle_rewards'], true);
      $new_game_data['values']['battle_rewards_hash'] = md5($this_database_save['save_values_battle_rewards']);
    }
    if (!empty($this_database_save['save_values_battle_settings'])){
      $new_game_data['values']['battle_settings'] = json_decode($this_database_save['save_values_battle_settings'], true);
      $new_game_data['values']['battle_settings_hash'] = md5($this_database_save['save_values_battle_settings']);
    }
    if (!empty($this_database_save['save_values_battle_items'])){
      $new_game_data['values']['battle_items'] = json_decode($this_database_save['save_values_battle_items'], true);
      $new_game_data['values']['battle_items_hash'] = md5($this_database_save['save_values_battle_items']);
    }
    if (!empty($this_database_save['save_values_battle_stars'])){
      $new_game_data['values']['battle_stars'] = json_decode($this_database_save['save_values_battle_stars'], true);
      $new_game_data['values']['battle_stars_hash'] = md5($this_database_save['save_values_battle_stars']);
    }
    if (!empty($this_database_save['save_values_robot_database'])){
      $new_game_data['values']['robot_database'] = json_decode($this_database_save['save_values_robot_database'], true);
      $new_game_data['values']['robot_database_hash'] = md5($this_database_save['save_values_robot_database']);
    }
    $new_game_data['flags'] = !empty($this_database_save['save_flags']) ? json_decode($this_database_save['save_flags'], true) : array();

    $new_game_data['battle_settings'] = !empty($this_database_save['save_settings']) ? json_decode($this_database_save['save_settings'], true) : array();

    // Update the session with the new save info
    $_SESSION[$session_token] = array_merge($_SESSION[$session_token], $new_game_data);
    unset($new_game_data);

    // Unset the player selection to restart at the player select screen
    if (mmrpg_prototype_players_unlocked() > 1){ $_SESSION[$session_token]['battle_settings']['this_player_token'] = false; }


  }
  // Otherwise, load from the file
  else {

    // LOAD SAVE FILE

    // Ensure the requested save path exists first
    if (file_exists($this_save_filepath)){
      // Read the save file into memory and collecy it's data
      $this_save_content = file_get_contents($this_save_filepath);
      // Ensure the save content was not empty
      if (!empty($this_save_content)){
        // Decode the data into a GAME array
        $this_save_content = json_decode($this_save_content, true);
        // Import the game content into the session
        $_SESSION[$session_token] = $this_save_content;
        unset($this_save_content);
        // Update the last load and saved value
        $_SESSION[$session_token]['values']['last_load'] = time();
        if (empty($_SESSION[$session_token]['values']['last_save'])){
          $_SESSION[$session_token]['values']['last_save'] = time();
        }
        // Return true on success
        return true;
      }
      // Otherwise, if the save content was empty
      else {
        // Return false on failure
        return false;
      }
    }
    // Otherwise, return false
    else {
      return false;
    }

  }

  // Update the last saved value
  $_SESSION[$session_token]['values']['last_load'] = time();

  // Update the user table in the database if not done already
  if (empty($_SESSION[$session_token]['DEMO'])){
    $DB->update('mmrpg_users', array(
    	'user_last_login' => time(),
      'user_backup_login' => $this_database_user['user_last_login'],
      ), "user_id = {$this_database_user['user_id']}");
  }

  //exit();

  // Return true on success
  return true;

}
?>