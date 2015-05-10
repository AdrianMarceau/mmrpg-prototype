<?
// If a user ID has been defined, attempt to swap the save session
if (!defined('MMRPG_REMOTE_GAME_ID')){ define('MMRPG_REMOTE_GAME_ID', (!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0)); }
if (MMRPG_REMOTE_GAME_ID != 0 && MMRPG_REMOTE_GAME_ID != $_SESSION['GAME']['USER']['userid']){

  // Attempt to collect data for this player from the database
  $this_playerinfo = $DB->get_array("SELECT
  	mmrpg_users.*,
  	mmrpg_saves.*
  	FROM mmrpg_users
  	LEFT JOIN mmrpg_saves ON mmrpg_saves.user_id = mmrpg_users.user_id
  	WHERE mmrpg_users.user_id = '".MMRPG_REMOTE_GAME_ID."';");

  // If the userinfo exists in the database, display it
  if (!empty($this_playerinfo)){

    // Ensure this remote game actually exists
    $temp_session_key = 'REMOTE_GAME_'.MMRPG_REMOTE_GAME_ID;
    // Define the constant that forces remote game checking
    define('MMRPG_REMOTE_GAME', MMRPG_REMOTE_GAME_ID);

    // Collect this player's info from the database... all of it
    $this_playerinfo['counters'] = !empty($this_playerinfo['save_counters']) ? json_decode($this_playerinfo['save_counters'], true) : array();
    $this_playerinfo['values'] = !empty($this_playerinfo['save_values']) ? json_decode($this_playerinfo['save_values'], true) : array();
    //$this_playerinfo['values']['battle_index'] = !defined('MMRPG_REMOTE_SKIP_INDEX') && !empty($this_playerinfo['save_values_battle_index']) ? json_decode($this_playerinfo['save_values_battle_index'], true) : array();
    $this_playerinfo['values']['battle_index'] = !defined('MMRPG_REMOTE_SKIP_INDEX') && !empty($this_playerinfo['save_values_battle_index']) ? $this_playerinfo['save_values_battle_index'] : array();
    $this_playerinfo['values']['battle_complete'] = !defined('MMRPG_REMOTE_SKIP_COMPLETE') && !empty($this_playerinfo['save_values_battle_complete']) ? json_decode($this_playerinfo['save_values_battle_complete'], true) : array();
    $this_playerinfo['values']['battle_failure'] = !defined('MMRPG_REMOTE_SKIP_FAILURE') && !empty($this_playerinfo['save_values_battle_failure']) ? json_decode($this_playerinfo['save_values_battle_failure'], true) : array();
    $this_playerinfo['values']['battle_rewards'] = !defined('MMRPG_REMOTE_SKIP_REWARDS') && !empty($this_playerinfo['save_values_battle_rewards']) ? json_decode($this_playerinfo['save_values_battle_rewards'], true) : array();
    $this_playerinfo['values']['battle_settings'] = !defined('MMRPG_REMOTE_SKIP_SETTINGS') && !empty($this_playerinfo['save_values_battle_settings']) ? json_decode($this_playerinfo['save_values_battle_settings'], true) : array();
    $this_playerinfo['values']['battle_items'] = !defined('MMRPG_REMOTE_SKIP_ITEMS') && !empty($this_playerinfo['save_values_battle_items']) ? json_decode($this_playerinfo['save_values_battle_items'], true) : array();
    $this_playerinfo['values']['battle_stars'] = !defined('MMRPG_REMOTE_SKIP_STARS') && !empty($this_playerinfo['save_values_battle_stars']) ? json_decode($this_playerinfo['save_values_battle_stars'], true) : array();
    $this_playerinfo['values']['robot_database'] = !defined('MMRPG_REMOTE_SKIP_DATABASE') && !empty($this_playerinfo['save_values_robot_database']) ? json_decode($this_playerinfo['save_values_robot_database'], true) : array();
    $this_playerinfo['flags'] = !empty($this_playerinfo['save_flags']) ? json_decode($this_playerinfo['save_flags'], true) : array();
    $this_playerinfo['settings'] = !empty($this_playerinfo['save_settings']) ? json_decode($this_playerinfo['save_settings'], true) : array();
    unset($this_playerinfo['save_values'],
      $this_playerinfo['save_values_battle_index'],
      $this_playerinfo['save_values_battle_complete'],
      $this_playerinfo['save_values_battle_failure'],
      $this_playerinfo['save_values_battle_rewards'],
      $this_playerinfo['save_values_battle_settings'],
      $this_playerinfo['save_values_battle_items'],
      $this_playerinfo['save_values_battle_database'],
      $this_playerinfo['save_flags'],
      $this_playerinfo['save_settings']
      );

    // Add this player's GAME data to the session for iframe scripts
    $temp_remote_session = array();
    $temp_remote_session['CACHE_DATE'] = !empty($_SESSION['GAME']['CACHE_DATE']) ? $_SESSION['GAME']['CACHE_DATE'] : MMRPG_CONFIG_CACHE_DATE;
    $temp_remote_session['DEMO'] = 0;
    $temp_remote_session['USER'] = array(
      'userid' => $this_playerinfo['user_id'],
      'username' => $this_playerinfo['user_name'],
      'username_clean' => $this_playerinfo['user_name_clean'],
      'imagepath' => $this_playerinfo['user_image_path'],
      'backgroundpath' => $this_playerinfo['user_background_path'],
      'colourtoken' => $this_playerinfo['user_colour_token'],
      'gender' => $this_playerinfo['user_gender'],
      'password' => '', //$this_playerinfo['user_id'],
      'password_encoded' => $this_playerinfo['user_password_encoded'],
      );
    $temp_remote_session['FILE'] = array(
      'path' => $this_playerinfo['user_name_clean'].'/',
      'name' => $this_playerinfo['user_password_encoded'],
      );
    $temp_remote_session['counters'] = $this_playerinfo['counters'];
    $temp_remote_session['values'] = $this_playerinfo['values'];
    $temp_remote_session['flags'] = $this_playerinfo['flags'];
    $temp_remote_session['settings'] = $this_playerinfo['settings'];
    //$temp_remote_session['USER'] = $this_playerinfo['settings'];
    //$temp_remote_session['FILE'] = $this_playerinfo['settings'];
    $temp_session_key = 'REMOTE_GAME_'.$this_playerinfo['user_id'];
    $_SESSION[$temp_session_key] = $temp_remote_session;
    //die('<pre>'.$temp_session_key.' : '.print_r($temp_remote_session, true).'</pre>');
    //die('<pre>'.$temp_session_key.' : '.print_r($_SESSION['REMOTE_GAME_'.$this_playerinfo['user_id']], true).'</pre>');

  }

}
?>