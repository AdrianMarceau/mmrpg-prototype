<?

// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
require(MMRPG_CONFIG_ROOTDIR.'frames/remote_top.php');

// Collect the session token
$session_token = rpg_game::session_token();

// Collect the editor flag if set
$global_allow_editing = isset($_GET['edit']) && $_GET['edit'] == 'false' ? false : true;


// -- COLLECT SETTINGS DATA -- //

// Define the editor indexes and count variables
$allowed_edit_players = array();
$allowed_edit_robots = array();
$allowed_edit_data = array();
$allowed_edit_data_count = 0;
$allowed_edit_player_count = 0;
$allowed_edit_robot_count = 0;

// Collect the player's robot favourites
$player_robot_favourites = rpg_game::robot_favourites();
if (empty($player_robot_favourites)){ $player_robot_favourites = array(); }

// Collect the player's robot database
$player_robot_database = rpg_game::robot_database();
if (empty($player_robot_database)){ $player_robot_database = array(); }

// Include the functions file for the editor
require('edit_robots_functions.php');

// Manually refresh all the editor arrays
refresh_editor_arrays(
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
    $allowed_edit_players, $allowed_edit_robots, $allowed_edit_data,
    $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count
    );


// -- GENERATE EDITOR MARKUP -- //

// Generate the canvas robots markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'canvas_markup'){
    require('edit_robots_canvas_robots.php');
}

// Generate the canvas players markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'canvas_players_markup'){
    require('edit_robots_canvas_players.php');
}

// Generate the canvas abilities markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'canvas_abilities_markup'){
    require('edit_robots_canvas_abilities.php');
}

// Generate the canvas items markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'canvas_items_markup'){
    require('edit_robots_canvas_items.php');
}

// Generate the console markup for this page
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'console_markup'){
    require('edit_robots_console_robots.php');
}

// Determine the token for the very first robot in the edit
$first_robot_token = $allowed_edit_robots[0];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Data Library | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/jquery.scrollbar.min.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/edit_robots.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" data-mode="<?= $global_allow_editing ? 'editor' : 'viewer' ?>">
    <div id="prototype" class="hidden" style="opacity: 0;">
        <div id="edit" class="menu" style="position: relative;">
            <div id="edit_overlay" style="">&nbsp;</div>
                <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                    <span class="count">
                        Robot <?= $global_allow_editing ? 'Editor' : 'Viewer' ?> (<?= $allowed_edit_robot_count == 1 ? '1 Robot' : $allowed_edit_robot_count.' Robots' ?>)
                    </span>
                </span>
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
                                            <div class="links"></div>
                                        </div>
                                        <div class="player_canvas" data-canvas="players">
                                            <div class="links"></div>
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
gameSettings.cacheTime = '<?= MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.autoScrollTop = false;
gameSettings.userNumber = <?= MMRPG_REMOTE_GAME_ID ?>;
gameSettings.allowEditing = <?= $global_allow_editing ? 'true' : 'false' ?>;
var countRobotLinks = false;
var countRobotsTriggered = 0;
var countRobotsLoaded = 0;
var countWrapperLoop = 0;
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'frames/remote_bottom.php');
// Unset the database variable
unset($db);
?>