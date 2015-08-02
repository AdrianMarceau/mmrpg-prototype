<?
// Make a reference to the current game
$session_token = mmrpg_game_token();

// Define the variable to hold all the prototype data
$prototype_data = array();

// Define the canvas and console markup variables
if (!isset($_SESSION[$session_token]['EVENTS'])){ $_SESSION[$session_token]['EVENTS'] = array(); }
$temp_canvas_markup = '';
$temp_console_markup = '';

// If it's not set, create a fake prototype start link
if (!isset($prototype_start_link)){ $prototype_start_link = 'home'; }
require(MMRPG_CONFIG_ROOTDIR.'data/prototype_vars.php');


/*
 * DEMO BATTLE OPTIONS
 */
if (!empty($_SESSION[$session_token]['DEMO'])){

  // Include the demo mode options and markup
  require(MMRPG_CONFIG_ROOTDIR.'prototype_demo_menu.php');

}
/*
 * NORMAL BATTLE OPTIONS
 */
else {

  // Require the omega factors
  require(MMRPG_CONFIG_ROOTDIR.'data/prototype_omega.php');

  // Do not process any events while we're in javascript mode
  if (!defined('MMRPG_SCRIPT_REQUEST')){

    // Include the demo mode options and markup
    require(MMRPG_CONFIG_ROOTDIR.'data/prototype_unlocks.php');

  }

  /*
   * MENU MARKUP
   */

  // DEMO MENU OPTIONS
  if (!empty($_SESSION[mmrpg_game_token()]['DEMO'])){
    // Only print out Light's data if conditions allow or do not exist
    if (empty($this_data_condition) || in_array('this_player_token=dr-light', $this_data_condition)){
      // Include the light mode options and markup
      if ($unlock_flag_light){
        require(MMRPG_CONFIG_ROOTDIR.'prototype_dr-light_menu.php');
      }
    }
  }
  // NORMAL MENU OPTIONS
  else {
    // Only print out Light's data if conditions allow or do not exist
    if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-light', $this_data_condition)){
      // Include the light mode options and markup
      if ($unlock_flag_light){
        require(MMRPG_CONFIG_ROOTDIR.'prototype_dr-light_menu.php');
      }
    }
    // Only print out Wily's data if conditions allow or do not exist
    if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-wily', $this_data_condition)){
      // Include the wily mode options and markup
      if ($unlock_flag_wily){
        require(MMRPG_CONFIG_ROOTDIR.'prototype_dr-wily_menu.php');
      }
    }
    // Only print out Cossack's data if conditions allow or do not exist
    if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-cossack', $this_data_condition)){
      // Include the cossack mode options and markup
      if ($unlock_flag_cossack){
        require(MMRPG_CONFIG_ROOTDIR.'prototype_dr-cossack_menu.php');
      }
    }
  }

}

?>