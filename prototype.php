<?php
// Include the TOP file
require_once('top.php');
/*
// MAINTENANCE
if (!in_array($_SERVER['REMOTE_ADDR'], array('99.226.253.166', '127.0.0.1', '99.226.238.61'))){
  die('<div style="margin: 0; padding: 10px 25%; background-color: rgb(122, 0, 0); color: #FFFFFF; text-align: left; border-bottom: 1px solid #090909;">
    ATTENTION!<br /> The Mega Man RPG Prototype is currently being updated.  Please stand by until further notice.  Several parts of the website are being taken offline during this process and any progress made during will likely be lost, so please hold tight before trying to log in again.  I apologize for the inconvenience.  Thank you and look forward to lots of new stuff!<br /> - Adrian
    </div>');
}
*/

// Require the robot database for... stuff?
//require_once('data/database.php');

// Collect the game's session token
$session_token = mmrpg_game_token();

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['PROTOTYPE_TEMP'] = array();
unset($_SESSION['GAME']['debug_mode']);

// Collect the prototype start link if provided
$prototype_start_link = !empty($_GET['start']) ? $_GET['start'] : 'home';

// Define the arrays for holding potential prototype messages
$prototype_window_event_canvas = array();
$prototype_window_event_messages = array();

// Check if a reset request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset'){
  // Reset the game session and reload the page
  mmrpg_reset_game_session($this_save_filepath);
  // Update the appropriate session variables
  $_SESSION['GAME']['USER'] = $this_user;
  $_SESSION['GAME']['FILE'] = $this_file;
  // Load the save file into memory and overwrite the session
  mmrpg_save_game_session($this_save_filepath);
  unset($DB);
  exit('success');
}
// Check if a reset request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset-missions' && !empty($_REQUEST['player'])){
  // Reset the appropriate session variables
  if (!empty($mmrpg_index['players'][$_REQUEST['player']])){
    $temp_session_key = $_REQUEST['player'].'_target-robot-omega_prototype';
    $_SESSION['GAME']['values']['battle_complete'][$_REQUEST['player']] = array();
    $_SESSION['GAME']['values']['battle_failure'][$_REQUEST['player']] = array();
    $_SESSION['GAME']['values'][$temp_session_key] = array();
  }
  // Load the save file into memory and overwrite the session
  mmrpg_save_game_session($this_save_filepath);
  unset($DB);
  exit('success');
}
// Check if a exit request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'exit'){
  // Auto-generate the user and file info based on their IP
  $this_user = array();
  $this_user['userid'] = MMRPG_SETTINGS_GUEST_ID;
  $this_user['username'] = 'demo';
  $this_user['username_clean'] = 'demo';
  $this_user['imagepath'] = '';
  $this_user['colourtoken'] = '';
  $this_user['gender'] = 'male';
  $this_user['password'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'demo';
  $this_user['password_encoded'] = md5($this_user['password']);
  $this_file = array();
  $this_file['path'] = $this_user['username_clean'].'/';
  $this_file['name'] = $this_user['password_encoded'].'.sav';
  // Update the session with these demo variables
  $_SESSION['GAME']['DEMO'] = 1;
  $_SESSION['GAME']['USER'] = $this_user;
  $_SESSION['GAME']['FILE'] = $this_file;
  $_SESSION['GAME']['counters']['battle_points'] = 0;
  // Update the global save path variable
  $this_save_filepath = $this_save_dir.$this_file['path'].$this_file['name'];
  // Reset the game session and reload the page
  mmrpg_reset_game_session($this_save_filepath);
  // Exit on success
  unset($DB);
  exit('success');
}

// Cache the currently online players
if (!isset($_SESSION['LEADERBOARD']['online_timestamp'])
  || (time() - $_SESSION['LEADERBOARD']['online_timestamp']) > 1){ // 600sec = 10min
  $_SESSION['LEADERBOARD']['online_players'] = mmrpg_prototype_leaderboard_online();
  $_SESSION['LEADERBOARD']['online_timestamp'] = time();
}


// Require the prototype data file
require_once('data/prototype.php');

/*
 * PASSWORD PROCESSING
 */

// Collect the game flags for easier password processing
$temp_flags = !empty($_SESSION['GAME']['flags']) ? $_SESSION['GAME']['flags'] : array();

/*
// Only proceed if there are actually flags to check
if (MMRPG_CONFIG_ADMIN_MODE && !empty($temp_flags)){
  // DEBUG PLAYERS / ABILITIES
  $mmrpg_index_players = $mmrpg_index['players'];

  // Collect the robot index for calculation purposes
  $this_robot_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

  // Collect the ability index for calculation purposes
  $this_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

  // DEBUG PLAYERS / ABILITIES
  foreach ($mmrpg_index_players AS $player_token => $player_info){
    $player_string = str_replace('-', '', $player_token);

    // DEBUG ABILITY UNLOCKS
    foreach ($this_ability_index AS $ability_token => $ability_info){
      if (mmrpg_prototype_ability_unlocked($player_token, false, $ability_token)){ continue; }
      $ability_info = mmrpg_ability::parse_index_info($ability_info);
      $ability_string = str_replace('-', '', $ability_token);
      if ($ability_token != 'ability' && !empty($temp_flags[$player_string.'_password_ability'.$ability_string])){
        if (!mmrpg_prototype_ability_unlocked($player_token, false, $ability_token)){
          mmrpg_game_unlock_ability($player_info, false, $ability_info, true);
          // And now redirect to the same page
          header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
          exit();
        }
      }
    }

    // DEBUG ROBOT UNLOCKS
    foreach ($this_robot_index AS $robot_token => $robot_info){
      if (mmrpg_prototype_robot_unlocked(false, $robot_token)){ continue; }
      $robot_info = mmrpg_robot::parse_index_info($robot_info);
      $robot_string = str_replace('-', '', $robot_token);
      if ($robot_token != 'robot' && !empty($temp_flags[$player_string.'_password_robot'.$robot_string])){
        // Unlock the requested robot
        mmrpg_game_unlock_robot($player_info, $robot_info, true);
        // And now redirect to the same page
        header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
        exit();
      }

    }

  }

  // Unset temporary indexes
  unset($this_robot_index, $this_ability_index);

}
*/

// Include the prototype awards file to check stuff
require('prototype_awards.php');


// If possible, attempt to save the game to the session
if (empty($_SESSION[$session_token]['DEMO']) && !empty($this_save_filepath)){
  mmrpg_save_game_session($this_save_filepath);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Prototype Menu | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/favicon<?= !MMRPG_CONFIG_IS_LIVE ? '-local' : '' ?>.ico">
<link type="text/css" href="styles/reset.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="prototype">

<div id="prototype" class="hidden">

  <div class="banner">
    <div class="sprite background banner_background" style="background-image: url(images/menus/menu-banner_this-battle-select.png);">&nbsp;</div>
    <div class="sprite foreground banner_foreground banner_dynamic" style="background-image: url(images/menus/prototype-banners_title-screen_01.gif?<?=MMRPG_CONFIG_CACHE_DATE?>); background-position: center -10px;">&nbsp;</div>
    <div class="sprite credits banner_credits" style="background-image: url(images/menus/menu-banner_credits.png?<?=MMRPG_CONFIG_CACHE_DATE?>);">Mega Man RPG Prototype | PlutoLighthouse.NET</div>
    <div class="sprite overlay overlay_hidden banner_overlay">&nbsp;</div>
    <div class="title">Mega Man RPG Prototype</div>

      <?
      // Define tooltips for the game options
      // Define the menu options array to be populated
      $this_menu_tooltips = array();
      $this_menu_tooltips['leaderboard'] = '&laquo; Battle Points Leaderboard &raquo; &lt;br /&gt;Live leaderboards rank all players by their total Battle Point scores from highest to lowest. Keep an eye on your Battle Points by checking here at the top-right of the main menu and try to work your way up to the first page!';
      $this_menu_tooltips['save'] = '&laquo; '.(!empty($_SESSION['GAME']['USER']['displayname']) ? $_SESSION['GAME']['USER']['displayname'] : $_SESSION['GAME']['USER']['username']).' Game Profile &raquo; &lt;br /&gt;Review and configure save file options including display name, theme and colour settings, robot avatars, missions resets, and more.  Please note that this game uses auto-save functionality and manual file updates are not required.';
      $this_menu_tooltips['database'] = '&laquo; Robot Database &raquo; &lt;br /&gt;A comprehensive list of all robots encountered in battle so far including their name, details, and records. Scanning robots adds their stats and weaknesses to the database and unlocking them adds a complete list of their level-up abilities.';
      $this_menu_tooltips['starforce'] = '&laquo; Star Force &raquo; &lt;br /&gt;A detailed list of all Field and Fusion Stars collected so far as well as a percentage-based breakdown of your current Star Force and its elemental affinities. Star Force increases the amount of damage inflicted by all elemental abilities of the same type!';
      $this_menu_tooltips['help'] = '&laquo; Battle Tips &raquo; &lt;br /&gt;A bullet-point list covering both basic and advanced battle tips to help you progress through the game and level up faster.';
      $this_menu_tooltips['demo'] = '&laquo; Demo Menu &raquo; &lt;br /&gt;Select your mission from the demo menu and prepare for battle! Please note that progress cannot be saved in this mode.';
      $this_menu_tooltips['home'] = '&laquo; Home Menu &raquo; &lt;br /&gt;Select your mission from the home menu and prepare for battle! Complete missions in fewer turns to earn more battle points!';
      $this_menu_tooltips['reset'] = '&laquo; Reset Game &raquo; &lt;br /&gt;Reset the demo mode back to the beginning and restart your adventure over from the first level.';
      $this_menu_tooltips['load'] = '&laquo; Load Game &raquo; &lt;br /&gt;Load an existing game file into memory and pick up where you left off during your last save.';
      $this_menu_tooltips['new'] = '&laquo; New Game &raquo; &lt;br /&gt;Create a new game file with a username and password to save progress and access the full version of the game.';
      $this_menu_tooltips['exit'] = '&laquo; Exit Game &raquo; &lt;br /&gt;Exit your save game and unload it from memory to return to the demo screen.';
      $this_menu_tooltips['robots'] = '&laquo; Robot Editor &raquo; &lt;br /&gt;Review detailed stats about your battle robots, equip them with new abilities, and transfer them to other players in your save file.';
      $this_menu_tooltips['players'] = '&laquo; Player Editor &raquo; &lt;br /&gt;Review detailed stats about your player characters and reconfigure chapter two battle fields to generate new field and fusion stars.';
      $this_menu_tooltips['abilities'] = '&laquo; Ability Viewer &raquo; &lt;br /&gt;...';
      $this_menu_tooltips['items'] = '&laquo; Item Inventory &raquo; &lt;br /&gt;...';
      $this_menu_tooltips['shop'] = '&laquo; Item Shop &raquo; &lt;br /&gt;Trade in your extra inventory for zenny in the shop and then put your earnings towards new items, new abilities, and new battle fields.';
      $temp_prototype_complete = mmrpg_prototype_complete();
      $temp_data_index = 0;
      ?>

    <div class="points field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <a class="wrapper link link_leaderboard" data-step="leaderboard" data-index="99" data-source="frames/leaderboard.php" data-music="misc/leader-board" data-tooltip="<?= $this_menu_tooltips['leaderboard'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <label class="label">Battle Points</label>
        <span class="amount">
          <? /*= preg_replace('#^([0]+)([0-9]+)$#', '<span class="padding">$1</span><span class="value">$2</span>', str_pad((!empty($_SESSION['GAME']['counters']['battle_points']) ? $_SESSION['GAME']['counters']['battle_points'] : 0), 13, '0', STR_PAD_LEFT)) */ ?>
          <?= number_format($_SESSION['GAME']['counters']['battle_points'], 0, '.', ',') ?>
          <? if(empty($_SESSION['GAME']['DEMO']) && !empty($this_boardinfo['board_rank'])): ?>
            <span class="pipe">|</span>
            <span class="place"><?= mmrpg_number_suffix($this_boardinfo['board_rank']) ?></span>
          <? endif; ?>
        </span>
      </a>
    </div>
    <div class="zenny field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <div class="wrapper">
        <span class="amount">
          <?= number_format($_SESSION['GAME']['counters']['battle_zenny'], 0, '.', ',') ?> z
        </span>
      </div>
    </div>

    <div class="options options_userinfo field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">

      <?
      // Define the avatar class and path variables
      $temp_avatar_path = !empty($_SESSION['GAME']['USER']['imagepath']) ? $_SESSION['GAME']['USER']['imagepath'] : 'robots/mega-man/40';
      $temp_colour_token = !empty($_SESSION['GAME']['USER']['colourtoken']) ? $_SESSION['GAME']['USER']['colourtoken'] : '';
      list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
      $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_00';
      $temp_sprite_offset = $temp_avatar_size == 80 ? 'margin-left: -20px; margin-top: -40px; ' : '';
      $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
      $temp_shadow_path = 'images/'.$temp_avatar_kind.'_shadows/'.preg_replace('/_(.*?)$/i', '', $temp_avatar_token).'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
      $temp_avatar_markup = '<span class="sprite sprite_40x40" style="bottom: 6px; right: 4px; z-index: 100; "><span class="'.$temp_sprite_class.'" style="background-image: url('.$temp_sprite_path.'); '.$temp_sprite_offset.'"></span></span>';
      $temp_avatar_markup .= '<span class="sprite sprite_40x40" style="bottom: 5px; right: 3px; z-index: 99; "><span class="'.$temp_sprite_class.'" style="background-image: url('.$temp_shadow_path.'); '.$temp_sprite_offset.'"></span></span>';
      ?>

      <? if(empty($_SESSION['GAME']['DEMO'])): ?>
        <a class="wrapper link link_save" data-step="file_save" data-index="98" data-source="frames/file.php?action=save" data-music="misc/file-menu" data-tooltip="<?= $this_menu_tooltips['save'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
          <span class="info info_userinfo">
            <span class="mode">
              <?
              // Print the prototype complete awards if they exist
              echo mmrpg_prototype_complete('dr-light') ? '<span style="position: relative; bottom: 1px;">&hearts;</span>' : '';
              echo mmrpg_prototype_complete('dr-wily') ? '<span style="position: relative; bottom: 1px;">&clubs;</span>' : '';
              echo mmrpg_prototype_complete('dr-cossack') ? '<span style="position: relative; bottom: 1px;">&diams;</span>' : '';
              // Print the player's rank based on leaderboard position
              if ($this_boardinfo['board_rank'] == 1){ echo ' Champion Rank'; }
              elseif ($this_boardinfo['board_rank'] <= 3){ echo ' Elite Rank'; }
              elseif ($this_boardinfo['board_rank'] <= 50){ echo ' Master Rank'; }
              elseif ($this_boardinfo['board_rank'] <= 100){ echo ' Golden Rank'; }
              elseif ($this_boardinfo['board_rank'] <= 200){ echo ' Silver Rank'; }
              elseif ($this_boardinfo['board_rank'] <= 400){ echo ' Bronze Rank'; }
              else { echo 'Normal Rank'; }
              ?>
            </span>
            <span class="name"><?= !empty($_SESSION['GAME']['USER']['displayname']) ? $_SESSION['GAME']['USER']['displayname'] : $_SESSION['GAME']['USER']['username'] ?></span>
          </span>
          <?= $temp_avatar_markup ?>
        </a>
      <? else: ?>
        <div class="wrapper">
          <span class="info info_username info_demo">
            <span class="mode">Welcome to the</span>
            <label title="Demo Mode : Progess cannot be saved!">Demo Mode</label>
          </span>
          <?= $temp_avatar_markup ?>
        </div>
      <? endif; ?>
    </div>

    <?
    // Check if the prototype has been completed before continuing
    $temp_prototype_complete = mmrpg_prototype_complete();
    ?>
    <div class="options options_fullmenu field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <div class="wrapper">
      <?
      // If we're in the DEMO MODE, define the available options and their attributes
      if (!empty($_SESSION['GAME']['DEMO'])){
        ?>
        <a class="link link_home link_active" data-step="2" data-index="<?= $temp_data_index++ ?>" data-music="misc/stage-select-dr-light" data-tooltip="<?= $this_menu_tooltips['demo'] ?>"><label>demo</label></a> <span class="pipe">|</span>
        <a class="link link_data" data-step="database" data-index="<?= $temp_data_index++ ?>" data-source="frames/database.php" data-music="misc/data-base" data-tooltip="<?= $this_menu_tooltips['database'] ?>"><label>database</label></a> <span class="pipe">|</span>
        <a class="link link_load" data-step="file_load" data-index="<?= $temp_data_index++ ?>" data-source="frames/file.php?action=load" data-music="misc/file-menu" data-tooltip="<?= $this_menu_tooltips['load'] ?>"><label>load</label></a> <span class="pipe">|</span>
        <a class="link link_new" href="file/new/" target="_blank"data-tooltip="<?= $this_menu_tooltips['new'] ?>"><label>new</label></a> <span class="pipe">|</span>
        <a class="link link_reset" data-index="<?= $temp_data_index++ ?>" data-tooltip="<?= $this_menu_tooltips['reset'] ?>"><label>reset</label></a>
        <?
      }
      // Otherwise, if we're in NORMAL MODE, we process the main menu differently
      else {
        ?>
        <a class="link link_home link_active" data-step="<?= $unlock_count_players == 1 ? 2 : 1 ?>" data-index="<?= $temp_data_index++ ?>" data-music="misc/<?= $unlock_count_players == 1 ? 'stage-select-dr-light' : 'player-select' ?>" data-tooltip="<?= $this_menu_tooltips['home'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>home</label></a> <span class="pipe">|</span>
        <? if(mmrpg_prototype_screws_unlocked() !== false && mmrpg_prototype_battles_complete() >= 3){ ?>
          <a class="link link_shop" data-step="shop" data-index="<?= $temp_data_index++ ?>" data-source="frames/shop.php" data-music="misc/shop-music" data-tooltip="<?= $this_menu_tooltips['shop'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>shop</label></a> <span class="pipe">|</span>
        <? } ?>
        <? if (mmrpg_prototype_robots_unlocked() >= 2){ ?>
          <a class="link link_robots" data-step="edit_robots" data-index="<?= $temp_data_index++ ?>" data-source="frames/edit_robots.php?action=robots" data-music="misc/robot-editor" data-tooltip="<?= $this_menu_tooltips['robots'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>robots</label></a> <span class="pipe">|</span>
        <? } ?>
        <?if (mmrpg_prototype_players_unlocked() >= 2){ ?>
          <a class="link link_players" data-step="edit_players" data-index="<?= $temp_data_index++ ?>" data-source="frames/edit_players.php?action=players" data-music="misc/player-editor" data-tooltip="<?= $this_menu_tooltips['players'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>players</label></a> <span class="pipe">|</span>
        <? } ?>
        <? if (false && mmrpg_prototype_items_unlocked() >= 1){ ?>
          <a class="link link_items" data-step="items" data-index="<?= $temp_data_index++ ?>" data-source="frames/items.php" data-music="misc/item-viewer" data-tooltip="<?= $this_menu_tooltips['items'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>items</label></a> <span class="pipe">|</span>
        <? } ?>
        <? if (false && mmrpg_prototype_abilities_unlocked() >= 3){ ?>
          <a class="link link_abilities" data-step="abilities" data-index="<?= $temp_data_index++ ?>" data-source="frames/abilities.php" data-music="misc/ability-viewer" data-tooltip="<?= $this_menu_tooltips['abilities'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>abilities</label></a> <span class="pipe">|</span>
        <? } ?>
        <? if(mmrpg_prototype_stars_unlocked() >= 1){ ?>
          <a class="link link_stars" data-step="starforce" data-index="<?= $temp_data_index++ ?>" data-source="frames/starforce.php" data-music="misc/star-force" data-tooltip="<?= $this_menu_tooltips['starforce'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>starforce</label></a> <span class="pipe">|</span>
        <? } ?>
        <? if(mmrpg_prototype_database_unlocked() >= 2){ ?>
          <a class="link link_data" data-step="database" data-index="<?= $temp_data_index++ ?>" data-source="frames/database.php" data-music="misc/data-base" data-tooltip="<?= $this_menu_tooltips['database'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><label>database</label></a> <span class="pipe">|</span>
        <? } ?>
        <a class="link link_exit" data-index="<?= $temp_data_index++ ?>" data-tooltip="<?= $this_menu_tooltips['exit'] ?>"><label>exit</label></a>
        <?
      }
      ?>
      </div>
    </div>

  </div>

  <div class="menu select_this_player" data-step="1" data-title="Player Select (<?= !empty($_SESSION['GAME']['DEMO']) || $unlock_count_players == 1 ? '1 Player' : $unlock_count_players.' Players' ?>)" data-select="this_player_token">
    <span class="header block_1">
      <span class="count">Player Select (<?= !empty($_SESSION['GAME']['DEMO']) || $unlock_count_players == 1 ? '1 Player' : $unlock_count_players.' Players' ?>)</span>
      <?/*<span class="reload">&#8634;</span>*/?>
    </span>
    <?
    // Require the prototype players display file
    require_once(MMRPG_CONFIG_ROOTDIR.'data/prototype_players.php');
    ?>
  </div>

  <div class="menu menu_hide select_this_battle" data-step="2" data-title="Battle Select" data-select="this_battle_token">
    <span class="header block_1">
      <span class="count"><?= !empty($_SESSION['GAME']['DEMO']) ? 'Mega Man RPG Prototype' : 'Mission Select' ?></span>
    </span>
    <?

    // Require the prototype missions display file
    require_once(MMRPG_CONFIG_ROOTDIR.'data/prototype_missions.php');

    // If we're NOT in demo mode, maybe add a back button
    if (empty($_SESSION['GAME']['DEMO'])){
      // Print out the back button for going back to player select
      if ($unlock_count_players > 1){
        echo '<a class="option option_back block_1" data-back="1">&#9668; Back</a>'."\n";
      }
    }

    ?>
  </div>

  <?
    /*
     * DEMO ROBOT SELECT
     */
    if (!empty($_SESSION['GAME']['DEMO'])){

      // Only show robot select if the player has more than two robots
      if (mmrpg_prototype_robots_unlocked('dr-light') > 3){

        // Print out the opening tags for the robot select container
        echo '<div class="menu menu_hide select_this_player_robots" data-step="3" data-limit="" data-title="Robot Select" data-select="this_player_robots">'."\n";
        echo '<span class="header block_1"><span class="count">Robot Select</span></span>'."\n";

        // Require the prototype robots display file
        require_once(MMRPG_CONFIG_ROOTDIR.'data/prototype_robots.php');

        // Print out the back button for going back to player select
        echo '<a class="option option_back block_1" data-back="2">&#9668; Back</a>'."\n";

        // Print out the closing tags for the robot select container
        echo '</div>'."\n";

      }

    }
    /*
     * NORMAL ROBOT SELECT
     */
    else {

      // Print out the opening tags for the robot select container
      echo '<div class="menu menu_hide select_this_player_robots" data-step="3" data-limit="" data-title="Robot Select" data-select="this_player_robots">'."\n";
      echo '<span class="header block_1"><span class="count">Robot Select</span></span>'."\n";

      // Require the prototype robots display file
      require_once(MMRPG_CONFIG_ROOTDIR.'data/prototype_robots.php');

      // Print out the back button for going back to player select
      echo '<a class="option option_back block_1" data-back="2">&#9668; Back</a>'."\n";

      // Print out the closing tags for the robot select container
      echo '</div>'."\n";

    }
  ?>

  <div class="menu menu_hide menu_file_new" data-step="file_new" data-source="frames/file.php?action=new"></div>

  <div class="menu menu_hide menu_file_load" data-step="file_load" data-source="frames/file.php?action=load"></div>

  <div class="menu menu_hide menu_file_save" data-step="file_save" data-source="frames/file.php?action=save"></div>

  <div class="menu menu_hide menu_shop" data-step="shop" data-source="frames/shop.php"></div>

  <div class="menu menu_hide menu_edit_robots" data-step="edit_robots" data-source="frames/edit_robots.php?action=robots"></div>

  <div class="menu menu_hide menu_edit_players" data-step="edit_players" data-source="frames/edit_players.php?action=players"></div>

  <div class="menu menu_hide menu_leaderboard" data-step="leaderboard" data-source="frames/leaderboard.php"></div>

  <div class="menu menu_hide menu_database" data-step="database" data-source="frames/database.php"></div>

  <div class="menu menu_hide menu_starforce" data-step="starforce" data-source="frames/starforce.php"></div>

  <div class="menu menu_hide menu_help" data-step="help" data-source="frames/help.php"></div>

  <div class="menu menu_hide menu_loading" data-step="loading" style="min-height: 600px;">
    <div class="option_wrapper option_wrapper_noscroll" style="color: white; font-weight: bold; line-height: 150px; letter-spacing: 4px; opacity: 0.75; margin-right: 0; background-color: rgba(0, 0, 0, 0.10); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; overflow: hidden; min-height: 600px; ">
      <div style="line-height: 40px; margin-top: 50px;">
        <span class="sprite sprite_40x40 sprite_40x40_left_00 " style="display: inline-block; position: static; background-image: url(images/assets/robot-loader_mega-man.gif); ">&nbsp;</span><br />
        <span class="label" style="display: inline-block;">loading</span>
      </div>
    </div>
  </div>

</div>

<div id="falloff" class="falloff_bottom">&nbsp;</div>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Define the game WAP and cache flags/values
gameSettings.passwordUnlocked = 0;
gameSettings.pointsUnlocked = <?= !empty($_SESSION['GAME']['counters']['battle_points']) ? $_SESSION['GAME']['counters']['battle_points'] : 0 ?>;
gameSettings.fadeIn = true;
gameSettings.demo = <?= $_SESSION['GAME']['DEMO'] ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.startLink = '<?= $prototype_start_link ?>';
gameSettings.windowEventsCanvas = [];
gameSettings.windowEventsMessages = [];
gameSettings.totalPlayerOptions = <?= $unlock_count_players ?>;
gameSettings.prototypeBannerKey = 0;
gameSettings.prototypeBanners = ['prototype-banners_title-screen_01.gif'];
// Define any preset menu selections
battleOptions['this_player_id'] = <?= $this_userid ?>;
<? if(!empty($_SESSION['GAME']['DEMO'])): ?>
  battleOptions['this_player_token'] = 'dr-light';
  <? if(mmrpg_prototype_robots_unlocked('dr-light') == 3): ?>
    battleOptions['this_player_robots'] = '103_mega-man,104_bass,105_proto-man';
  <?endif;?>
<? else: ?>
  <? if(!empty($_SESSION['GAME']['battle_settings']['this_player_token'])): ?>
    battleOptions['this_player_token'] = '<?=$_SESSION['GAME']['battle_settings']['this_player_token']?>';
  <? elseif($unlock_count_players < 2): ?>
    battleOptions['this_player_token'] = 'dr-light';
  <? endif; ?>
<? endif; ?>
// Create the document ready events
$(document).ready(function(){

  <? if($prototype_start_link == 'home' && empty($_SESSION['GAME']['battle_settings']['this_player_token'])): ?>
    // Start playing the title screen music
    parent.mmrpg_music_load('misc/player-select', true, false);
  <? elseif($prototype_start_link != 'home'): ?>
    <? if(!empty($_SESSION['GAME']['battle_settings']['this_player_token'])): ?>
      // Start playing the appropriate stage select music
      parent.mmrpg_music_load('misc/stage-select-<?= $_SESSION['GAME']['battle_settings']['this_player_token'] ?>', true, false);
    <? else: ?>
      // Start playing the title screen music
      parent.mmrpg_music_load('misc/player-select', true, false);
    <? endif; ?>
  <? endif; ?>

  <?
  // If there were any prototype window events created, display them
  if (!empty($_SESSION['GAME']['EVENTS'])){
    foreach ($_SESSION['GAME']['EVENTS'] AS $temp_key => $temp_event){
      $temp_canvas_markup = str_replace('"', '\"', $temp_event['canvas_markup']);
      $temp_messages_markup =  str_replace('"', '\"', $temp_event['console_markup']);
      echo 'gameSettings.windowEventsCanvas.push("'.$temp_canvas_markup.'");'."\n";
      echo 'gameSettings.windowEventsMessages.push("'.$temp_messages_markup.'");'."\n";
    }
  }
  ?>

});
</script>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/data/analytics.php');
?>
</body>
</html>
<?
// If there were any events in the session, automatically add remove them from the session
if (!empty($_SESSION['GAME']['EVENTS'])){ $_SESSION['GAME']['EVENTS'] = array(); }
// Unset the database variable
unset($DB);
?>