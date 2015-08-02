<?
// Define a function for resetting the game session
function mmrpg_reset_game_session($this_save_filepath){
  // Reference global variables
  global $mmrpg_index, $DB;
  //$GAME_SESSION = &$_SESSION[mmrpg_game_token()];
  $session_token = mmrpg_game_token();

  // Back up the user and file info from the session
  $this_demo = $_SESSION[$session_token]['DEMO'];
  $this_user = $_SESSION[$session_token]['USER'];
  $this_file = $_SESSION[$session_token]['FILE'];
  $this_level_bonus = mmrpg_prototype_robot_level('dr-light', 'mega-man');
  $this_battle_points = !empty($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;
  $this_battle_zenny = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
  $this_battle_items = !empty($_SESSION[$session_token]['counters']['battle_items']) ? $_SESSION[$session_token]['counters']['battle_items'] : array();
  $this_battle_stars = !empty($_SESSION[$session_token]['counters']['battle_stars']) ? $_SESSION[$session_token]['counters']['battle_stars'] : array();
  $this_battle_abilities = !empty($_SESSION[$session_token]['counters']['battle_abilities']) ? $_SESSION[$session_token]['counters']['battle_abilities'] : array();
  $this_battle_complete = !empty($_SESSION[$session_token]['values']['battle_complete']) ? $_SESSION[$session_token]['values']['battle_complete'] : array();
  $this_battle_failure = !empty($_SESSION[$session_token]['values']['battle_failure']) ? $_SESSION[$session_token]['values']['battle_failure'] : array();
  $this_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
  $this_battle_rewards = !empty($_SESSION[$session_token]['values']['battle_rewards']) ? $_SESSION[$session_token]['values']['battle_rewards'] : array();
  $this_battle_items = !empty($_SESSION[$session_token]['values']['battle_items']) ? $_SESSION[$session_token]['values']['battle_items'] : array();
  $this_index_settings = !empty($_SESSION[$session_token]['index_settings']) ? $_SESSION[$session_token]['index_settings'] : array();

  // Automatically unset the session variable entirely
  session_unset();
  // Automatically create the cache date
  $_SESSION[$session_token] = array();
  $_SESSION[$session_token]['CACHE_DATE'] = MMRPG_CONFIG_CACHE_DATE;
  // Redefine the user and file variables in the new session
  $_SESSION[$session_token]['DEMO'] = $this_demo;
  $_SESSION[$session_token]['USER'] = $this_user;
  $_SESSION[$session_token]['FILE'] = $this_file;
  // Automatically create the battle points counter and start at zero
  $_SESSION[$session_token]['counters']['battle_points'] = (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset') ? 0 : $this_battle_points;
  // Automatically create the battle points counter and start at zero
  $_SESSION[$session_token]['counters']['battle_zenny'] = (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset') ? 0 : $this_battle_zenny;
  // Automatically create the battle complete array and start at empty
  $_SESSION[$session_token]['values']['battle_complete'] = array();
  // Automatically create the battle failure array and start at empty
  $_SESSION[$session_token]['values']['battle_failure'] = array();
  // Automatically create the battle index array and start at empty
  $_SESSION[$session_token]['values']['battle_index'] = array();
  // Automatically create the battle items array and start at empty
  $_SESSION[$session_token]['values']['battle_items'] = (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset') ? array() : $this_battle_items;
  // Automatically create the battle stars array and start at empty
  $_SESSION[$session_token]['values']['battle_stars'] = (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset') ? array() : $this_battle_stars;
  // Automatically create the battle abilities array and start at empty
  $_SESSION[$session_token]['values']['battle_abilities'] = (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset') ? array() : $this_battle_abilities;
  // Automatically create the robot database array and start at empty
  $_SESSION[$session_token]['values']['robot_database'] = array();
  // Automatically create the index settings array and start at what was before
  $_SESSION[$session_token]['index_settings'] = $this_index_settings;
  // Automatically create the last load and save variable and set to now
  $_SESSION[$session_token]['values']['last_load'] = time();
  $_SESSION[$session_token]['values']['last_save'] = time();

  // -- DEMO MODE UNLOCKS -- //
  if (!empty($_SESSION[$session_token]['DEMO'])){

    // Reset the demo flag and user id to defaul
    $_SESSION[$session_token]['USER']['userid'] = MMRPG_SETTINGS_GUEST_ID;
    $_SESSION[$session_token]['DEMO'] = 1;

    // Only unlock Dr. Light as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-light'];
    mmrpg_game_unlock_player($unlock_player_info, false, true);
    $_SESSION[$session_token]['values']['battle_rewards']['dr-light']['player_points'] = 0;
    $_SESSION[$session_token]['values']['battle_items'] = array('item-energy-pellet' => 3, 'item-energy-capsule' => 2, 'item-weapon-pellet' => 3, 'item-weapon-capsule' => 2);
    // Auto-select Dr. Light as the current playable character
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = 'dr-light';

    // Collect the robot index for calculation purposes
    $this_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

    // Unlock Mega Man as a playable character
    $unlock_robot_info = $this_robot_index['mega-man'];
    $unlock_robot_info['robot_level'] = 1;
    $unlock_robot_info['robot_rewards']['abilities'] = array(
        array('level' => 0, 'token' => 'super-throw'),
        array('level' => 0, 'token' => 'fire-storm'),
        array('level' => 0, 'token' => 'hyper-bomb'),
        array('level' => 0, 'token' => 'ice-breath'),
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'mega-buster'),
        array('level' => 0, 'token' => 'mega-ball'),
        array('level' => 0, 'token' => 'mega-slide')
        );
    //$unlock_robot_info['robot_level'] = 5;
    //echo __LINE__.print_r($_SESSION[$session_token]['values']['battle_rewards']['dr-light'], true);
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, false);

    // Unlock Bass as a playable character
    $unlock_robot_info = $this_robot_index['bass'];
    $unlock_robot_info['robot_level'] = 1;
    $unlock_robot_info['robot_rewards']['abilities'] = array(
        array('level' => 0, 'token' => 'air-shooter'),
        array('level' => 0, 'token' => 'leaf-shield'),
        array('level' => 0, 'token' => 'bubble-spray'),
        array('level' => 0, 'token' => 'quick-boomerang'),
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'bass-buster'),
        array('level' => 0, 'token' => 'bass-crush'),
        array('level' => 0, 'token' => 'bass-baroque')
        );
    //$unlock_robot_info['robot_level'] = 99;
    //$unlock_robot_info['robot_experience'] = mmrpg_prototype_calculate_experience_required(1) - 1;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, false);

    // Unlock Proto Man as a playable character
    $unlock_robot_info = $this_robot_index['proto-man'];
    $unlock_robot_info['robot_level'] = 1;
    $unlock_robot_info['robot_rewards']['abilities'] = array(
        array('level' => 0, 'token' => 'drill-blitz'),
        array('level' => 0, 'token' => 'bright-burst'),
        array('level' => 0, 'token' => 'dive-missile'),
        array('level' => 0, 'token' => 'skull-barrier'),
        array('level' => 0, 'token' => 'buster-shot'),
        array('level' => 0, 'token' => 'proto-buster'),
        array('level' => 0, 'token' => 'proto-shield'),
        array('level' => 0, 'token' => 'proto-strike')
        );
    //$unlock_robot_info['robot_level'] = 99;
    //$unlock_robot_info['robot_experience'] = mmrpg_prototype_calculate_experience_required(1) - 1;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, false);

  }
  // -- NORMAL MODE UNLOCKS -- //
  else {

    // Unlock Dr. Light as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-light'];
    mmrpg_game_unlock_player($unlock_player_info, true, true);
    $_SESSION[$session_token]['values']['battle_rewards']['dr-light']['player_points'] = 0;
    $_SESSION[$session_token]['values']['battle_items'] = array();
    // Auto-select Dr. Light as the current playable character
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = 'dr-light';
    // Unlock Mega Man as a playable character
    $unlock_robot_info = mmrpg_robot::get_index_info('mega-man');;
    $unlock_robot_info['robot_level'] = 1; //!empty($this_level_bonus) ? $this_level_bonus : 1;
    $unlock_robot_info['robot_experience'] = mmrpg_prototype_calculate_experience_required(1) - 1;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, false);

  }

  // Destroy the cached save file
  if (!empty($this_save_filepath) && file_exists($this_save_filepath)){ @unlink($this_save_filepath); }
  // Return true on success
  return true;
}
?>