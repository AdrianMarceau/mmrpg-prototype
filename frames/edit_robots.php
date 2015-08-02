<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
//define('MMRPG_REMOTE_SKIP_DATABASE', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Include the DATABASE file
//require_once('../data/database.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_types.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_players.php');
//require(MMRPG_CONFIG_ROOTDIR.'data/database_robots.php');
$mmrpg_database_robots = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1 ORDER BY robot_order ASC;", 'robot_token');
//require(MMRPG_CONFIG_ROOTDIR.'data/database_abilities.php');
$mmrpg_database_abilities = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_class = 'master' ORDER BY ability_order ASC;", 'ability_token');
//require(MMRPG_CONFIG_ROOTDIR.'data/database_items.php');
$mmrpg_database_items = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_class = 'item' ORDER BY ability_order ASC;", 'ability_token');
// Collect the editor flag if set
$global_allow_editing = isset($_REQUEST['edit']) && $_REQUEST['edit'] == 'false' ? false : true;


/*
// -- CONVERT ROBOT ABILITIES INTO PLAYER ABILITIES -- //

// If we're NOT in script mode, let's loop through all the robots and make sure
if ((empty($_REQUEST['action']) || $_REQUEST['action'] == 'robots') && !defined('MMRPG_REMOTE_GAME')){
  // Include the robot update file if necessary
	if ($global_allow_editing){
	  require('edit_robots_updates.php');
	}

}
*/

// -- COLLECT SETTINGS DATA -- //

// Define the editor indexes and count variables
$allowed_edit_players = array();
$allowed_edit_robots = array();
$allowed_edit_data = array();
$allowed_edit_data_count = 0;
$allowed_edit_player_count = 0;
$allowed_edit_robot_count = 0;

// Collect the player's robot favourites
$player_robot_favourites = mmrpg_prototype_robot_favourites();
if (empty($player_robot_favourites)){ $player_robot_favourites = array(); }

// Collect the player's robot database
$player_robot_database = mmrpg_prototype_robot_database();
if (empty($player_robot_database)){ $player_robot_database = array(); }

// Include the functions file for the editor
require('edit_robots_functions.php');

// Trigger parsing of relevant editor indexes
parse_editor_indexes(
  $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities,
  $allowed_edit_players, $allowed_edit_robots, $allowed_edit_data
  );
// Manually refresh all the editor arrays
refresh_editor_arrays(
  $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities,
  $allowed_edit_players, $allowed_edit_robots, $allowed_edit_data,
  $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count
  );
// -- PROCESS PLAYER ACTION -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'player'){
  require('edit_robots_action_player.php');
}

// -- PROCESS ABILITY ACTION -- //

// Check if an action request has been sent with an ability type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'ability'){
  require('edit_robots_action_ability.php');
}

// -- PROCESS ITEM ACTION -- //

// Check if an action request has been sent with an item type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'item'){
  require('edit_robots_action_item.php');
}


// -- PROCESS ALTIMAGE ACTION -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'altimage'){
  require('edit_robots_action_altimage.php');
}

// -- PROCESS FAVOURITE ACTION -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'favourite'){
  require('edit_robots_action_favourite.php');
}

// -- PROCESS SORT ACTION -- //

// Check if an action request has been sent with an player type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'sort'){
  require('edit_robots_action_sort.php');
}



// -- RECOLLECT SETTINGS DATA -- //

// Manually refresh all the editor arrays
refresh_editor_arrays(
  $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities,
  $allowed_edit_players, $allowed_edit_robots, $allowed_edit_data,
  $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count
  );
// -- GENERATE EDITOR MARKUP

// CANVAS ROBOTS MARKUP

// Generate the canvas robots markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'canvas_markup'){
  require('edit_robots_canvas_robots_markup.php');
}

// CANVAS ABILITIES MARKUP

// Generate the canvas abilities markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'canvas_abilities_markup'){
  require('edit_robots_canvas_abilities_markup.php');
}

// CANVAS ITEMS MARKUP

// Generate the canvas items markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'canvas_items_markup'){
  require('edit_robots_canvas_items_markup.php');
}

// CONSOLE ROBOTS MARKUP

// Generate the console markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'console_markup'){
  require('edit_robots_console_robots_markup.php');
}

// Determine the token for the very first robot in the edit
$first_robot_token = $allowed_edit_robots[0];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title><?= !MMRPG_CONFIG_IS_LIVE ? '@ ' : '' ?><?= $global_allow_editing ? 'Edit' : 'View' ?> Robots | Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/favicon<?= !MMRPG_CONFIG_IS_LIVE ? '-local' : '' ?>.ico">
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/jquery.scrollbar.min.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/edit_robots.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
  <div id="prototype" class="hidden" style="opacity: 0; <?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">
    <div id="edit" class="menu" style="position: relative;">
      <div id="edit_overlay" style="">&nbsp;</div>
        <span class="header block_1">Robot <?= $global_allow_editing ? 'Editor' : 'Viewer' ?> (<?= $allowed_edit_robot_count == 1 ? '1 Robot' : $allowed_edit_robot_count.' Robots' ?>)</span>
        <div class="section">
          <table class="formatter">
            <colgroup>
              <col width="220" />
              <col width="" />
            </colgroup>
            <tbody>
              <tr>
                <td class="console">
                  <div id="console" class="noresize">
                    <div id="robots" class="wrapper"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="canvas">
                  <div id="canvas">
                    <div class="robot_canvas" data-canvas="robots">
                      <table class="links"><tr></tr></table>
                    </div>
                    <div class="ability_canvas" data-canvas="abilities">
                      <div class="links"></div>
                    </div>
                    <div class="item_canvas" data-canvas="items">
                      <div class="links"></div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
    </div>
  </div>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.sortable.min.js"></script>
<script type="text/javascript" src="scripts/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/edit_robots.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'true' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.autoScrollTop = false;
gameSettings.userNumber = <?= MMRPG_REMOTE_GAME_ID ?>;
gameSettings.allowEditing = <?= $global_allow_editing ? 'true' : 'false' ?>;
var countRobotLinks = false;
var countRobotsTriggered = 0;
var countRobotsLoaded = 0;
var countWrapperLoop = 0;
<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'unlocked-tooltip_robot-editor-intro';
if (empty($temp_game_flags[$temp_event_flag]) && $global_allow_editing){
  $temp_game_flags[$temp_event_flag] = true;
  ?>
//Wait until the document is ready
$(document).ready(function(){
  // Generate a first-time event canvas that explains how the editor works
  gameSettings.windowEventsCanvas = [
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/field/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/ice-slasher/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 20px; left: 0px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/fire-storm/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 35px; left: 100px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/mega-buster/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 50px; left: 200px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/rolling-cutter/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 35px; left: 300px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/abilities/hyper-bomb/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 20px; left: 400px; width: 80px; height: 80px;">&nbsp;</div>'+
    ''
    ];
  // Generate a first-time event message that explains how the editor works
  gameSettings.windowEventsMessages = [
    '<p>The <strong>Robot Editor</strong> contains detailed information on all of your unlocked robot masters and allows you to edit their attributes.  Detailed stat breakdowns track the growth of your robtos while the weaknesses and resistances provide helpful reference before battle. The most powerful feature of the editor, however, comes in the form of ability customization.</p>'+
    '<p>Click on any of any of the eight weapon slots for a robot and you can equip it with any ability it\'s compatible with - based on its core type - even if the ability was originally learned by another robot. Some abilities can be used by all robots and some by only a select few, so don\'t be afraid to experiment when a new one is unlocked.</p>'+
    ''
    ];
  // Push this event to the parent window and display to the user
  top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
});
  <?
}
?>
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($DB);
?>