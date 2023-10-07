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
//debug_profiler_checkpoint('prototype-include/before-vars');
if (!isset($prototype_start_link)){ $prototype_start_link = 'home'; }
require(MMRPG_CONFIG_ROOTDIR.'prototype/vars.php');
//debug_profiler_checkpoint('prototype-include/after-vars');

//die('<pre>'.print_r($temp_game_flags, true).'</pre>');
//die('<pre>'.print_r($chapters_unlocked_light, true).'</pre>');

/*
 * NORMAL BATTLE OPTIONS
 */

// Require the omega factors
//debug_profiler_checkpoint('prototype-include/before-omega');
require(MMRPG_CONFIG_ROOTDIR.'prototype/omega.php');
//debug_profiler_checkpoint('prototype-include/after-omega');

// Do not process any events while we're in javascript mode
if (!defined('MMRPG_SCRIPT_REQUEST')){

    // Include the demo mode options and markup
    //debug_profiler_checkpoint('prototype-include/before-unlocks');
    require(MMRPG_CONFIG_ROOTDIR.'prototype/unlocks.php');
    //debug_profiler_checkpoint('prototype-include/after-unlocks');

}

/*
 * CAMPAIGN MENU MARKUP
 */

// Only print out Light's data if conditions allow or do not exist
//debug_profiler_checkpoint('prototype-include/before-campaigns');
if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-light', $this_data_condition)){
    // Include the light mode options and markup
    if ($unlock_flag_light){
        //debug_profiler_checkpoint('prototype-include/before-light-campaign');
        require(MMRPG_CONFIG_ROOTDIR.'prototype/campaigns/dr-light.php');
        //debug_profiler_checkpoint('prototype-include/after-light-campaign');
    }
}
// Only print out Wily's data if conditions allow or do not exist
if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-wily', $this_data_condition)){
    // Include the wily mode options and markup
    if ($unlock_flag_wily){
        //debug_profiler_checkpoint('prototype-include/before-wily-campaign');
        require(MMRPG_CONFIG_ROOTDIR.'prototype/campaigns/dr-wily.php');
        //debug_profiler_checkpoint('prototype-include/after-wily-campaign');
    }
}
// Only print out Cossack's data if conditions allow or do not exist
if (empty($this_data_condition) || $this_data_select == 'this_player_token' || in_array('this_player_token=dr-cossack', $this_data_condition)){
    // Include the cossack mode options and markup
    if ($unlock_flag_cossack){
        //debug_profiler_checkpoint('prototype-include/before-cossack-campaign');
        require(MMRPG_CONFIG_ROOTDIR.'prototype/campaigns/dr-cossack.php');
        //debug_profiler_checkpoint('prototype-include/after-cossack-campaign');
    }
}
//debug_profiler_checkpoint('prototype-include/after-campaigns');

?>