<?php

/*
// MAINTENANCE
if (!in_array($_SERVER['REMOTE_ADDR'], array('999.999.999.999'))){
  die('<div style="margin: 0; padding: 10px 25%; background-color: rgb(122, 0, 0); color: #FFFFFF; text-align: left; border-bottom: 1px solid #090909;">
    ATTENTION!<br /> The Mega Man RPG Prototype is currently being updated.  Please stand by until further notice.  Several parts of the website are being taken offline during this process and any progress made during will likely be lost, so please hold tight before trying to log in again.  I apologize for the inconvenience.  Thank you and look forward to lots of new stuff!<br /> - Adrian
    </div>');
}
*/

/*
 * GLOBAL INCLUDES
 */

// Start the session
@date_default_timezone_set('Canada/Eastern');
//@ini_set('session.gc_maxlifetime', 24*60*60);
//@ini_set('session.gc_probability', 1);
//@ini_set('session.gc_divisor', 1);
session_start();

// Include mandatory config files
define('MMRPG_BUILD', 'mmrpg2k15');
define('MMRPG_VERSION', '3.0.0');
require('data/config.php');
require('data/settings.php');
require('data/debug.php');

// Turn on error reporting
if (MMRPG_CONFIG_ADMIN_MODE){
  ini_set('display_errors',1);
  ini_set('display_startup_errors',1);
  error_reporting(-1);
}

// Include the database class first and foremost
require('data/classes/database.php');
// Create the global database object
if (!defined('MMRPG_INDEX_SESSION') && !defined('MMRPG_INDEX_STYLES')){
  if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG'] = array(); }
  $DB = new plutocms_database();
  // If the database could not be created, critical error mode!
  if ($DB->CONNECT === false){
    define('MMRPG_CRITICAL_ERROR', true);
    $_GET = array();
    $_GET['page'] = 'error';
    //die('the database could not be accessed!');
  }
}

// Include mandatory class files
require('data/classes/battle.php');
require('data/classes/field.php');
require('data/classes/player.php');
require('data/classes/robot.php');
require('data/classes/ability.php');
require('data/classes/item.php');

// Include mandatory function files
require('data/functions/system.php');
require('data/functions/website.php');
require('data/functions/game.php');
require('data/functions/prototype.php');

/*
 * LIBRARY INDEXES
 */

// If we're in a file page, prevent userinfo caching
if (preg_match('/file.php$/i', basename(__FILE__))){
  // Prevent userinfo caching for this page
  unset($_SESSION['GAME']['USER']['userinfo']);
}

// Include mandatory library files
$mmrpg_index = array();
//require('data/battles/_index.php');
//require('data/fields/_index.php');
require('data/players/_index.php');
//require('data/robots/_index.php');
//require('data/abilities/_index.php');
require('data/types/_index.php');

// Turn off magic quotes before it causes and problems
if (get_magic_quotes_gpc()){
  $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
  while (list($key, $val) = each($process)) {
    foreach ($val as $k => $v) {
      unset($process[$key][$k]);
      if (is_array($v)) {
        $process[$key][stripslashes($k)] = $v;
        $process[] = &$process[$key][stripslashes($k)];
      } else {
        $process[$key][stripslashes($k)] = stripslashes($v);
      }
    }
  }
  unset($process);
}


/*
 * SESSION VARIABLES
 */

// Create mandatory session variables if they do not exist
if (!isset($_SESSION['BATTLES'])){ $_SESSION['BATTLES'] = array(); }
if (!isset($_SESSION['FIELDS'])){ $_SESSION['FIELDS'] = array(); }
if (!isset($_SESSION['PLAYERS'])){ $_SESSION['PLAYERS'] = array(); }
if (!isset($_SESSION['ROBOTS'])){ $_SESSION['ROBOTS'] = array(); }
if (!isset($_SESSION['ABILITIES'])){ $_SESSION['ABILITIES'] = array(); }
// Define the COMMUNITY session trackers if they do not exist
if (!isset($_SESSION['COMMUNITY'])){ $_SESSION['COMMUNITY']['threads_viewed'] = array(); }

/*
 * BROWSER FLAGS
 */

// Define the WAP flag to false
$flag_wap = false;
$flag_ipad = false;
$flag_iphone = false;
// Collect the WAP flag if set in the URL query
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
if (isset($_GET['wap'])){ $flag_wap = $_GET['wap'] == 'true' ? true : false; }
//Otherwise, check if this is an iPhone or iPad browser
elseif (!empty($_GET['iphone']) || strpos($user_agent, 'iPhone') !== FALSE){ $flag_iphone = true; }
elseif (!empty($_GET['ipad']) || strpos($user_agent, 'iPad')){	$flag_ipad = $flag_wap = true; }
unset($user_agent);

/*
 * GAME SAVING AND LOADING
 */

// Only continue with saving/loading functions if we're NOT in critical mode
if (!defined('MMRPG_CRITICAL_ERROR')){

  // Disable the memory limit for this script
  //ini_set('memory_limit', '128M');
  //ini_set('memory_limit', '-1');

  // Define the first load boolean variable
  $this_first_load = false;
  // Define the game cache location path
  $this_cache_dir = MMRPG_CONFIG_ROOTDIR.'data/cache/';
  // Define the game save location path
  $this_save_dir = MMRPG_CONFIG_ROOTDIR.'data/saves/';

  // If the user and file details have already been loaded to the session
  if (
    !empty($_SESSION['GAME']['USER']) && !empty($_SESSION['GAME']['USER']['username']) && !empty($_SESSION['GAME']['USER']['password'])
    && !empty($_SESSION['GAME']['FILE']) && !empty($_SESSION['GAME']['FILE']['path']) && !empty($_SESSION['GAME']['FILE']['name'])
    ){
    // Pull the user and file info from the session
    $this_user = $_SESSION['GAME']['USER'];
    $this_file = $_SESSION['GAME']['FILE'];
    // Update the save filepath with the file path and name
    $this_save_filepath = $this_save_dir.$this_file['path'].$this_file['name'];
  }
  // Otherwise, if we're in demo mode, populate manually
  else {
    // Auto-generate the user and file info based on their IP
    $this_user = array();
    $this_user['userid'] = MMRPG_SETTINGS_GUEST_ID;
    $this_user['username'] = 'demo';
    $this_user['username_clean'] = 'demo';
    $this_user['imagepath'] = '';
    $this_user['colourtoken'] = '';
    $this_user['gender'] = '';
    $this_user['password'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'demo';
    $this_user['password_encoded'] = md5($this_user['password']);
    $this_file = array();
    $this_file['path'] = $this_user['username_clean'].'/';
    $this_file['name'] = $this_user['password_encoded'].'.sav';
    // Update the session with these demo variables
    $_SESSION['GAME']['DEMO'] = 1;
    $_SESSION['GAME']['USER'] = $this_user;
    $_SESSION['GAME']['FILE'] = $this_file;
    // Update the first load to indicate true
    $this_first_load = true;
    // Update the global save path variable
    $this_save_filepath = $this_save_dir.$this_file['path'].$this_file['name'];
  }

}

/*
 * PAGE REQUESTS
 */

// Collect the current page from the header if set
$this_allowed_pages = array('home', 'about', 'gallery', 'database', 'leaderboard', 'community', 'prototype', 'credits', 'contact', 'file', 'error');
$this_current_page = !empty($_GET['page']) ? strtolower($_GET['page']) : false;
$this_current_sub = !empty($_GET['sub']) ? strtolower($_GET['sub']) : false;
$this_current_num = !empty($_GET['num']) ? $_GET['num'] : 1;
$this_current_token = !empty($_GET['token']) ? $_GET['token'] : '';
$this_current_cat = !empty($_GET['cat']) ? $_GET['cat'] : '';
$this_current_id = !empty($_GET['id']) ? $_GET['id'] : 0;

// Redirect the stupid friggin home pages to their friggin not home page versions (UGH!)
//die('<pre>'.print_r($_GET, true).'</pre>');
$this_current_uri = !empty($this_current_page) && $this_current_page != 'home' ? $this_current_page.'/' : '';
$this_current_uri .= !empty($this_current_sub) && $this_current_sub != 'home' ? $this_current_sub.'/' : '';
if ($this_current_page != 'community'){ $this_current_uri .= !empty($this_current_num) && $this_current_num > 1 ? $this_current_num.'/' : ''; }
if (isset($_GET['home']) || $this_current_sub == 'home' || $this_current_page == 'home'){
  $_GET['this_redirect'] = $this_current_url;
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: '.$this_current_url);
  exit();
} elseif ($this_current_page == 'about'){
  $this_current_sub = !empty($_GET['sub']) ? strtolower($_GET['sub']) : '';
  if (!empty($this_current_sub)){
    $this_current_uri .= $this_current_sub.'/';
  }
} elseif ($this_current_page == 'updates'){
  $this_current_id = !empty($_GET['id']) ? strtolower($_GET['id']) : 0;
  $this_current_token = !empty($_GET['token']) ? $_GET['token'] : '';
  if ($this_current_id !== false && !empty($this_current_token)){
    $this_current_uri .= $this_current_id.'/'.$this_current_token.'/';
  }
} elseif ($this_current_page == 'community'){
  $this_current_cat = !empty($_GET['cat']) ? strtolower($_GET['cat']) : '';
  $this_current_id = !empty($_GET['id']) ? strtolower($_GET['id']) : 0;
  $this_current_token = !empty($_GET['token']) ? $_GET['token'] : '';
  $this_current_target = !empty($_GET['target']) ? $_GET['target'] : '';
  if (!empty($this_current_cat) && $this_current_id !== false && !empty($this_current_token)){
    $this_current_uri .= $this_current_cat.'/'.$this_current_id.'/'.$this_current_token.'/';
  } elseif (!empty($this_current_cat) && !empty($this_current_sub)){
    $this_current_uri = $this_current_page.'/'.$this_current_cat.'/'.$this_current_sub.'/';
  } elseif (!empty($this_current_cat)){
    $this_current_uri .= $this_current_cat.'/';
  }
  if (!empty($this_current_target)){
    //$this_current_uri .= $this_current_target.'/';
  }
  if (!empty($this_current_num) && $this_current_num != 1){
    $this_current_uri .= $this_current_num.'/';
  }
} elseif ($this_current_page == 'database'){
  $this_current_token = !empty($_GET['token']) ? $_GET['token'] : '';
  if (!empty($this_current_token) && isset($mmrpg_index['types'][$this_current_token]) || in_array($this_current_token, array('multi', 'bonus'))){
    $this_current_filter = $_GET['filter'] = $this_current_token;
    $this_current_filter_name = $this_current_filter == 'none' ? 'Neutral' : ucfirst($this_current_filter);
    $this_current_uri .= $this_current_filter.'/';
    $this_current_token = $_GET['token'] = '';
  } elseif (!empty($this_current_token)){
    $this_current_uri .= $this_current_token.'/';
  }
} elseif ($this_current_page == 'leaderboard'){
  $this_current_token = !empty($_GET['token']) ? $_GET['token'] : '';
  if (!empty($this_current_token)){
    $this_current_uri .= $this_current_token.'/';
  }
} elseif ($this_current_page == 'file'){
  $this_current_token = !empty($_GET['token']) ? $_GET['token'] : '';
  if (!empty($this_current_token)){
    $this_current_uri .= $this_current_token.'/';
  }
}
$this_current_url = MMRPG_CONFIG_ROOTURL.$this_current_uri;
$_GET['this_current_uri'] = $this_current_uri; //urlencode($this_current_uri);
$_GET['this_current_url'] = $this_current_url; //urlencode($this_current_url);
//die('<pre>'.print_r($_GET, true).'</pre>');

// Now that all the redirecting is done, if the current page it totally empty, it's ACTUALLY home
if (empty($this_current_page) || !in_array($this_current_page, $this_allowed_pages)){ $this_current_page = 'home'; }

/*
 * USERINFO COLLECTION
 */

// If we're NOT viewing the session info
if (!defined('MMRPG_CRITICAL_ERROR') && !defined('MMRPG_INDEX_SESSION') && !defined('MMRPG_INDEX_SESSION') && !defined('MMRPG_INDEX_STYLES')){

  // If the user session is already in progress, collect the details
  if (!empty($_SESSION['GAME']['USER']['userid']) && $_SESSION['GAME']['USER']['userid'] != MMRPG_SETTINGS_GUEST_ID){

    // Collect this userinfo from the database
    $this_userid = (int)($_SESSION['GAME']['USER']['userid']);
    if (empty($_SESSION['GAME']['USER']['userinfo'])){
      $this_userinfo = $DB->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");
      $_SESSION['GAME']['USER']['userinfo'] = $this_userinfo;
    } else {
      $this_userinfo = $_SESSION['GAME']['USER']['userinfo'];
    }

    if (!defined('MMRPG_SCRIPT_REQUEST')){
      $this_boardinfo = $DB->get_array("SELECT * FROM mmrpg_leaderboard WHERE user_id = {$this_userid}");
      $this_boardid = $this_boardinfo['board_id'];
      $this_boardinfo['board_rank'] = !empty($_SESSION['GAME']['BOARD']['boardrank']) ? $_SESSION['GAME']['BOARD']['boardrank'] : 0;
      //if (empty($this_boardinfo['board_rank'])){ require('data/leaderboard.php'); $_SESSION['GAME']['BOARD']['boardrank'] = $this_boardinfo['board_rank']; }
      if (empty($this_boardinfo['board_rank'])){ $_SESSION['GAME']['BOARD']['boardrank'] = $this_boardinfo['board_rank'] = mmrpg_prototype_leaderboard_rank($this_userid); }
    }

  }
  // Otherwise, generate some details user details
  else {

    // Collect the guest userinfo from the database
    $this_userid = MMRPG_SETTINGS_GUEST_ID;
    if (empty($_SESSION['GAME']['USER']['userinfo'])){
      $this_userinfo = $DB->get_array("SELECT users.* FROM mmrpg_users AS users WHERE users.user_id = '{$this_userid}' LIMIT 1");
      $_SESSION['GAME']['USER']['userinfo'] = $this_userinfo;
    } else {
      $this_userinfo = $_SESSION['GAME']['USER']['userinfo'];
    }

    if (!defined('MMRPG_SCRIPT_REQUEST')){
      $this_boardinfo = array();
      $this_boardinfo['board_rank'] = 0;
      $this_boardid = 0;
    }

  }

} else {
  // Create the userinfo array anyway to prevent errors
  $this_userinfo = array();
}

/*
 * WEBSITE THEME GENERATION
 */

// If we're NOT viewing the session info
if (!defined('MMRPG_INDEX_SESSION') && !defined('MMRPG_INDEX_STYLES')){

  // Select a random background image to display
  $temp_field_path = !empty($this_userinfo['user_background_path']) ? $this_userinfo['user_background_path'] : 'fields/intro-field';
  $temp_field_type = !empty($this_userinfo['user_colour_token']) ? $this_userinfo['user_colour_token'] : '';
  list($temp_field_kind, $temp_field_token) = explode('/', $temp_field_path);

  // Collect the info for the chosen temp field
  $temp_field_data = mmrpg_field::get_index_info($temp_field_token);
  //die('<pre>'.print_r($temp_field_data, true).'</pre>');
  //die('<pre>'.print_r($this_userinfo, true).'</pre>');
  // Define the current field token for the index
  define('MMRPG_SETTINGS_CURRENT_FIELDTOKEN', $temp_field_data['field_token']);
  define('MMRPG_SETTINGS_CURRENT_FIELDTYPE', (!empty($temp_field_type) ? $temp_field_type : (!empty($temp_field_data['field_type']) ? $temp_field_data['field_type'] : 'none')));
  define('MMRPG_SETTINGS_CURRENT_FIELDFRAMES', count($temp_field_data['field_background_frame']));
  define('MMRPG_SETTINGS_CURRENT_FIELDMECHA', (!empty($temp_field_data['field_mechas']) ? $temp_field_data['field_mechas'][0] : 'met'));
  //die('$temp_field_type = '.$temp_field_type.'; MMRPG_SETTINGS_CURRENT_FIELDTYPE = '.MMRPG_SETTINGS_CURRENT_FIELDTYPE);

}

?>
