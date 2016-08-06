<?
// Define the SCRIPT REQUEST constant for later reference
define('MMRPG_SCRIPT_REQUEST', true);

// Require the application top file
require_once('../top.php');

// DEBUG DEBUG DEBUG
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Collect the request data from the headers
$this_data_step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : false;
$this_data_select = !empty($_REQUEST['select']) ? $_REQUEST['select'] : false;
$this_data_condition = !empty($_REQUEST['condition']) ? $_REQUEST['condition'] : array();

// If either are empty, kill the script without hesistation
if (empty($this_data_step)){ die('error:missing-data-step'); }
elseif (empty($this_data_select)){ die('error:missing-data-select'); }
//elseif (empty($this_data_condition)){ die('error:missing-data-condition'); }

// If the condition if not empty, break it apart
if (!empty($this_data_condition)){
  if (strstr($this_data_condition, '|')){ $this_data_condition = explode('|', $this_data_condition); }
  else { $this_data_condition = array($this_data_condition); }
}
// Decode any URL encoded conditional signs
foreach ($this_data_condition AS $key => $string){
  $this_data_condition[$key] = str_replace('%3d', '=', $string);
}

// DEBUG DEBUG DEBUG
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Otherwise, require the prototype data file
require_once(MMRPG_CONFIG_ROOTDIR.'prototype/include.php');

// DEBUG DEBUG DEBUG
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Start the output buffer
ob_start();

// Decide which data to include base on select kind
switch ($this_data_select){

  // If this was a PLAYERS request type, print out the players
  case 'this_player_token': {

    // Require the prototype players display file
    require_once(MMRPG_CONFIG_ROOTDIR.'prototype/players.php');

    // DEBUG
    //exit('success:players-requested');

    // DEBUG DEBUG DEBUG
    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

    // Break automatically to prevent looping
    break;

  }

  // If this was a MISSIONS request type, print out the missions
  case 'this_battle_token': {

    // Require the prototype missions display file
    require_once(MMRPG_CONFIG_ROOTDIR.'prototype/missions.php');

    // DEBUG
    //exit('missions-requested:'.print_r($this_data_condition, true));

    // DEBUG DEBUG DEBUG
    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

    // Break automatically to prevent looping
    break;
  }

  // If this was a ROBOTS request type, print out the robots
  case 'this_player_robots': {

    // Require the prototype robots display file
    require_once(MMRPG_CONFIG_ROOTDIR.'prototype/robots.php');

    // DEBUG
    //exit('success:robots-requested');

    // DEBUG DEBUG DEBUG
    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }


    // Break automatically to prevent looping
    break;
  }

}

// Collect the buffer contents
$temp_markup = ob_get_clean();

// Unset the database variable
unset($db);

// If the user made it this far, exit gracefully
exit(!empty($temp_markup) ? $temp_markup : '');

?>