<?php

// Include the TOP file
//require('admin/includes/debug_profiler_top.php');
//debug_profiler_checkpoint('before-top');
require_once('top.php');
//debug_profiler_checkpoint('after-top');

// If the user is not logged in, don't allow them here
if (!rpg_game::is_user()){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'frames/login.php');
    exit();
}

// Collect the game's session token
$session_token = mmrpg_game_token();

// Pull in necessary indexes in case we need them later
if (!isset($mmrpg_index_players) || empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

// Apply any patches that need to be applied on start (should only need to run once)
mmrpg_prototype_apply_patches();

// Restore any dropped items to their owners if able to
//mmrpg_prototype_restore_dropped_items();
//debug_profiler_checkpoint('after-restore-items');

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['BATTLES_CHAIN'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ROBOTS_PRELOAD'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['ITEMS_DROPPED'] = array();
$_SESSION['SKILLS'] = array();
$_SESSION['ENDLESS'] = array();
$_SESSION['STARS'] = array();
$_SESSION['PROTOTYPE_TEMP'] = array();

// Collect the prototype start link if provided
$prototype_start_link = !empty($_GET['start']) ? $_GET['start'] : 'home';

// Define the arrays for holding potential prototype messages
$prototype_window_event_canvas = array();
$prototype_window_event_messages = array();

//debug_profiler_checkpoint('before-actions');

// Check if a reset request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset'){

    // Require the appropriate reset file
    require(MMRPG_CONFIG_ROOTDIR.'prototype/reset.php');

}
// Check if a reset request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'reset-missions' && !empty($_REQUEST['player'])){

    // Require the appropriate reset file
    require(MMRPG_CONFIG_ROOTDIR.'prototype/reset-missions.php');

}
// Check if a new-game-plus request has been placed
if (!empty($_REQUEST['action'])
    && $_REQUEST['action'] == 'new-game-plus'
    && !empty($_REQUEST['reset'])){
    error_log('new-game-plus: '.var_export($_REQUEST, true));

    // Require the appropriate reset file
    require(MMRPG_CONFIG_ROOTDIR.'prototype/reset-plus.php');

}

// Check if a exit request has been placed
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'exit'){

    // Exit the game and enter demo mode
    rpg_game::exit_session();

    // Exit on success
    unset($db);
    exit('success');

}

//debug_profiler_checkpoint('after-actions');

// Cache the currently online players
if (!isset($_SESSION['LEADERBOARD']['online_timestamp'])
    || (time() - $_SESSION['LEADERBOARD']['online_timestamp']) > 1){ // 600sec = 10min
    $_SESSION['LEADERBOARD']['online_players'] = mmrpg_prototype_leaderboard_online();
    $_SESSION['LEADERBOARD']['online_timestamp'] = time();
}

// Require the prototype data file
require_once('prototype/include.php');
//debug_profiler_checkpoint('after-prototype-include');

// Include the prototype awards file to check stuff
//debug_profiler_checkpoint('before-awards');
require(MMRPG_CONFIG_ROOTDIR.'prototype/awards.php');
//debug_profiler_checkpoint('after-awards');

// If possible, attempt to save the game to the session
//debug_profiler_checkpoint('before-refresh-points-and-save');
if (rpg_game::is_user()){
    $old_points = !empty($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;
    //debug_profiler_checkpoint('before-save-game');
    mmrpg_save_game_session(); // the game automatically refreshes battle points on save
    //debug_profiler_checkpoint('after-save-game');
    $new_points = !empty($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;
    if ($old_points != $new_points){
        header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
        exit();
    }
}
//debug_profiler_checkpoint('after-refresh-points-and-save');

// Check to see if the prototype is "complete" for display purposes
$temp_prototype_complete = mmrpg_prototype_complete();
//debug_profiler_checkpoint('after-check-prototype-complete');

// Define tooltips for the game options that appear in the main menu and surrounding UI
$this_menu_tooltips = array();
$this_menu_tooltips['home'] = '&laquo; Home Menu &raquo; &lt;br /&gt;Select your mission from the home menu and prepare for battle! Complete missions in fewer turns to earn more zenny!';
$this_menu_tooltips['shop'] = '&laquo; Item Shop &raquo; &lt;br /&gt;Trade in your extra inventory for zenny in the shop and then put your earnings towards new items, new abilities, and new battle fields.';
$this_menu_tooltips['robots'] = '&laquo; Robot Editor &raquo; &lt;br /&gt;Review detailed stats about your battle robots, equip them with new abilities, and transfer them to other players in your save file.';
$this_menu_tooltips['players'] = '&laquo; Player Editor &raquo; &lt;br /&gt;Review detailed stats about your player characters and reconfigure chapter two battle fields to generate new field and fusion stars.';
$this_menu_tooltips['abilities'] = '&laquo; Ability Arsenal &raquo; &lt;br /&gt;View your archive of abilities unlocked so far, including types, power, descriptions, and images.';
$this_menu_tooltips['items'] = '&laquo; Item Inventory &raquo; &lt;br /&gt;View your inventory of collected items thus far, including quantities, descriptions, and images.';
$this_menu_tooltips['stars'] = '&laquo; Star Collection &raquo; &lt;br /&gt;A detailed list of all the Field and Fusion Stars collected on your journey so far. Collect many different stars to advance in the prototype!';
$this_menu_tooltips['database'] = '&laquo; Robot Database &raquo; &lt;br /&gt;A comprehensive list of all robots encountered in battle so far including their name and basic details. Scanning robots adds their stats and weaknesses to the database and unlocking them adds a complete list of their level-up abilities.';
$this_menu_tooltips['save'] = '&laquo; Game Settings &raquo; &lt;br /&gt;Update your game settings and profile options including username, password, profile colour, and more.';
$this_menu_tooltips['leaderboard'] = '&laquo; Battle Points Leaderboard &raquo; &lt;br /&gt;Live leaderboards ranking all players by their total Battle Point scores from highest to lowest. Keep an eye on your Battle Points by checking the top-right of the main menu and try to work your way up to the first page!';

// Generate a list of data-index values based on predefined menu option order
$temp_data_index = 0;
$this_menu_indexes = array();
foreach ($this_menu_tooltips AS $token => $text){
    $this_menu_indexes[$token] = $temp_data_index++;
}

//debug_profiler_checkpoint('before-html-body');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Main Menu | Prototype | Mega Man RPG Prototype</title>

<base href="<?=MMRPG_CONFIG_ROOTURL?>" />

<meta name="robots" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />

<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">

<link type="text/css" href="styles/reset.css" rel="stylesheet" />

<style type="text/css"> html, body { background-color: #262626; } </style>

<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />

<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/ready-room.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />

<link rel="apple-touch-icon" sizes="72x72" href="images/assets/ipad-icon-2k19_72x72.png" />
<? /* <meta name="viewport" content="user-scalable=yes, width=768, height=1004"> */ ?>
<meta name="viewport" content="user-scalable=yes, width=device-width, min-width=768, initial-scale=1">

</head>
<?

// Collect the number of missions complete and the number or robots unlocked by this player
$total_missions_complete = mmrpg_prototype_battles_complete(false, true);
$total_player_options = $unlock_count_players;
$total_robot_options = mmrpg_prototype_robots_unlocked();

// Check to see if the ready room should be unlocked yet or not
$ready_room_unlocked = false;
if ($total_missions_complete >= 2){ $ready_room_unlocked = true; }

// Decide whether to use an animated or static background for prototype home
$prototype_banner_image = 'prototype-banners_title-screen_01.gif';
if ($ready_room_unlocked){ $prototype_banner_image = 'prototype-banners_title-screen_01.png'; }

// Collect and prototype-menu settings from the session for display
$session_token = rpg_game::session_token();
$battleSettings = $_SESSION[$session_token]['battle_settings'];
$spriteRenderMode = isset($battleSettings['spriteRenderMode']) ? $battleSettings['spriteRenderMode'] : 'default';
$battleButtonMode = isset($battleSettings['battleButtonMode']) ? $battleSettings['battleButtonMode'] : 'default';

?>
<body id="mmrpg" class="prototype <?= 'env_'.MMRPG_CONFIG_SERVER_ENV ?>">

<div id="prototype" class="hidden" data-render-mode="<?= $spriteRenderMode ?>" data-button-mode="<?= $battleButtonMode ?>">
    <div class="bgfx-layer layer-1"></div>
    <div class="bgfx-layer layer-2"></div>

    <div class="banner">
        <div class="sprite background banner_background" style="background-image: url(images/menus/menu-banner_this-battle-select.png);">&nbsp;</div>
        <div class="sprite foreground banner_foreground banner_dynamic" style="background-image: url(images/menus/<?= $prototype_banner_image ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-position: center -10px;">&nbsp;</div>
        <div class="sprite credits banner_credits"><h1>Mega Man RPG Prototype | Browser-Based Battle Simulator</h1></div>
        <div class="sprite overlay overlay_hidden banner_overlay">&nbsp;</div>

        <div class="title">Mega Man RPG Prototype</div>

        <?
        // Check to see if a Rogue Star is currently in orbit
        $this_rogue_star = mmrpg_prototype_get_current_rogue_star();
        if (!empty($this_rogue_star)){
            $star_type = $this_rogue_star['star_type'];
            $star_name = ucfirst($star_type);
            $now_time = time();
            $star_from_time = strtotime($this_rogue_star['star_from_date'].'T'.$this_rogue_star['star_from_date_time']);
            $star_to_time = strtotime($this_rogue_star['star_to_date'].'T'.$this_rogue_star['star_to_date_time']);
            $star_time_duration = $star_to_time - $star_from_time;
            $star_time_elapsed = $now_time - $star_from_time;
            $star_time_elapsed_percent = ($star_time_elapsed / $star_time_duration) * 100;
            $star_time_remaining = $star_time_duration - $star_time_elapsed;
            $star_position_right = (100 - $star_time_elapsed_percent) + 1;
            $star_minutes_left = ($star_time_remaining / 60);
            $star_hours_left = ($star_minutes_left / 60);
            $star_tooltip = '&raquo; Rogue Star Event! &laquo; || A '.$star_name.'-type Rogue Star has appeared! This star grants +'.$this_rogue_star['star_power'].' '.$star_name.'-type Starforce for a limited time. Take advantage of its power before it\'s gone! ';
            if ($star_hours_left >= 1){ $star_tooltip .= 'You have less than '.($star_hours_left > 1 ? ceil($star_hours_left).' hours' : '1 hour').' remaining! '; }
            elseif ($star_hours_left < 1){ $star_tooltip .= 'You have only '.($star_minutes_left > 1 ? ceil($star_minutes_left).' minutes' : '1 minute').' remaining! ';  }
            ?>
            <div class="sprite rogue_star"
                data-star-type="<?= $star_type ?>"
                data-from-date="<?= $this_rogue_star['star_from_date'] ?>"
                data-from-date-time="<?= $this_rogue_star['star_from_date_time'] ?>"
                data-to-date="<?= $this_rogue_star['star_to_date'] ?>"
                data-to-date-time="<?= $this_rogue_star['star_to_date_time'] ?>"
                data-star-power="<?= $this_rogue_star['star_power'] ?>"
                data-click-tooltip="<?= $star_tooltip ?>"
                data-tooltip-type="type_<?= $star_type ?>">
                <div class="wrap">
                    <div class="label">
                        <div class="name type_empty">Rogue Star!</div>
                        <div class="effect type_<?= $star_type ?>"><?= ucfirst($star_type) ?> +<?= $this_rogue_star['star_power'] ?></div>
                    </div>
                    <div class="sprite track type_<?= $star_type ?>"></div>
                    <div class="sprite trail type_<?= $star_type ?>" style="right: <?= $star_position_right ?>%;"></div>
                    <div class="sprite ruler"></div>
                    <div class="sprite star" style="background-image: url(images/items/fusion-star_<?= $star_type ?>/sprite_right_40x40.png); right: <?= $star_position_right ?>%; right: calc(<?= $star_position_right ?>% - 20px);"></div>
                </div>
            </div>
            <?
        }
        ?>

        <?

        // Collect the current points, zenny, and star counts to determine if overflow is needed
        $battle_points_count = isset($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;
        $battle_zenny_count = isset($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
        $battle_stars_count = isset($_SESSION[$session_token]['values']['battle_stars']) ? count($_SESSION[$session_token]['values']['battle_stars']) : 0;
        $battle_points_rank = !empty($_SESSION[$session_token]['BOARD']['boardrank']) ? $_SESSION[$session_token]['BOARD']['boardrank'] : 0;
        $has_points_overflow = $battle_points_count >= 999999999999 ? true : false;
        $has_zenny_overflow = $battle_zenny_count >= 999999999 ? true : false;

        // If player battles have been unlocked (via the Light Program), make sure we collect the token count
        $battle_tokens_count = 0;
        if (mmrpg_prototype_item_unlocked('light-program')){
            $battle_tokens_count = $db->get_value("SELECT
                COUNT(battles.target_user_id) AS players_defeated
                FROM mmrpg_battles AS battles
                INNER JOIN mmrpg_users AS users ON battles.target_user_id = users.user_id
                INNER JOIN mmrpg_leaderboard AS board ON battles.target_user_id = board.user_id
                WHERE
                    battles.this_user_id = {$this_userid}
                    AND battles.target_user_id <> {$this_userid}
                    AND battles.this_player_result = 'victory'
                    AND battles.battle_flag_legacy = 0
                    AND users.user_flag_approved = 1
                    AND board.board_points > 0
                ;", 'players_defeated');
            if ($battle_tokens_count > 0){ $has_zenny_overflow = true; }
        }

        // If the user has collected any points, turn the points counter into a leaderboard link
        $battle_points_container_attrs = array();
        if ($battle_points_count > 0 && $battle_points_rank > 0){
            $battle_points_container_attrs[] = 'data-step="leaderboard"';
            $battle_points_container_attrs[] = 'data-index="'.$this_menu_indexes['leaderboard'].'"';
            $battle_points_container_attrs[] = 'data-source="frames/leaderboard.php"';
            $battle_points_container_attrs[] = 'data-music="misc/leader-board"';
            $battle_points_container_attrs[] = 'data-maybe-tooltip="'.$this_menu_tooltips['leaderboard'].'"';
            $battle_points_container_attrs[] = 'data-tooltip-type="field_type field_type_'.MMRPG_SETTINGS_CURRENT_FIELDTYPE.'"';
        }
        $battle_points_container_attrs = implode(' ', $battle_points_container_attrs);

        ?>
        <div class="points field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?> <?= $has_points_overflow || $has_zenny_overflow ? 'overflow' : '' ?>" <?= $battle_points_container_attrs ?>>
            <div class="wrapper">
                <label class="label">Battle Points</label>
                <span class="amount">
                    <span class="value"><?= number_format($battle_points_count, 0, '.', ',') ?></span>
                    <? if(!empty($battle_points_rank)): ?>
                        <span class="pipe">|</span>
                        <span class="place"><?= mmrpg_number_suffix($battle_points_rank) ?></span>
                    <? endif; ?>
                </span>
            </div>
        </div>
        <div class="subpoints field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?> <?= $has_zenny_overflow ? 'overflow' : '' ?>">
            <div class="wrapper">
                <span class="amount zenny">
                    <?= (!empty($battle_zenny_count) ? number_format($battle_zenny_count, 0, '.', ',') : 0).'&#438;' ?>
                </span>
                <? if (!empty($battle_tokens_count)){ ?>
                    <span class="pipe">|</span>
                    <span class="amount tokens"><?=
                        '<span class="num">'.number_format($battle_tokens_count, 0, '.', ',').'</span>'.
                        '<i class="fa fa-fw fa-stop-circle"></i>'
                        ?></span>
                <? } ?>
                <? if (!empty($_SESSION[$session_token]['values']['battle_stars'])){ ?>
                    <span class="pipe">|</span>
                    <span class="amount stars"><?=
                        '<span class="num">'.number_format($battle_stars_count, 0, '.', ',').'</span>'.
                        '<i class="fa fas fa-star"></i>'
                        ?></span>
                <? } ?>
                <? if (mmrpg_prototype_item_unlocked('omega-seed')){ ?>
                    <span class="pipe">|</span>
                    <span class="amount omega">
                        <i class="fa fas fa-greek-omega"></i>
                    </span>
                <? } ?>
            </div>
        </div>

        <div class="options options_userinfo field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" data-step="file_save" data-index="<?= $this_menu_indexes['save'] ?>" data-source="frames/settings.php" data-music="misc/file-menu" data-maybe-tooltip="<?= $this_menu_tooltips['save'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <div class="wrapper">
                <? $temp_user_name = !empty($this_userinfo['user_name_public']) && !empty($this_userinfo['user_flag_postpublic']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name']; ?>
                <div class="info info_username">
                    <label><?= $temp_user_name ?></label>
                </div>
                <div class="config">
                    <i class="fa fas fa-cog"></i>
                </div>
            </div>
            <?
            // Define the avatar class and path variables
            $temp_avatar_path = !empty($_SESSION[$session_token]['USER']['imagepath']) ? $_SESSION[$session_token]['USER']['imagepath'] : 'robots/mega-man/40';
            list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
            $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_00';
            $temp_sprite_offset = $temp_avatar_size == 80 ? 'margin-left: -20px; margin-top: -40px; ' : '';
            $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $temp_shadow_path = 'images/'.$temp_avatar_kind.'_shadows/'.preg_replace('/_(.*?)$/i', '', $temp_avatar_token).'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
            ?>
            <div class="sprite_wrapper">
                <span class="sprite base sprite_40x40"><span class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>); <?= $temp_sprite_offset ?>"></span></span>
                <span class="sprite shadow sprite_40x40"><span class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>); <?= $temp_sprite_offset ?>"></span></span>
            </div>
        </div>

        <?
        // Check if the prototype has been completed before continuing
        $temp_prototype_complete = mmrpg_prototype_complete();
        ?>
        <div class="options options_fullmenu field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <div class="wrapper">

                <a class="link link_home link_active" data-step="<?= $unlock_count_players == 1 ? 2 : 1 ?>" data-index="<?= $this_menu_indexes['home'] ?>" data-music="misc/<?= $unlock_count_players == 1 ? 'stage-select-dr-light' : 'player-select' ?>" data-maybe-tooltip="<?= $this_menu_tooltips['home'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                    <i class="fa fas fa-home"></i>
                    <label>home</label>
                </a>
                <? if (mmrpg_prototype_item_unlocked('auto-link')
                    || mmrpg_prototype_item_unlocked('reggae-link')
                    || mmrpg_prototype_item_unlocked('kalinka-link')): ?>
                    <span class="pipe">|</span>
                    <a class="link link_shop" data-step="shop" data-index="<?= $this_menu_indexes['shop'] ?>" data-source="frames/shop.php" data-music="misc/shop-music" data-maybe-tooltip="<?= $this_menu_tooltips['shop'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-shopping-cart"></i>
                        <label>shop</label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_robots_unlocked() > 1 || mmrpg_prototype_battles_complete('dr-light') >= MMRPG_SETTINGS_CHAPTER1_MISSIONS): ?>
                    <span class="pipe">|</span>
                    <a class="link link_robots" data-step="edit_robots" data-index="<?= $this_menu_indexes['robots'] ?>" data-source="frames/edit_robots.php?action=robots" data-music="misc/robot-editor" data-maybe-tooltip="<?= $this_menu_tooltips['robots'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-robot"></i>
                        <label><?= mmrpg_prototype_robots_unlocked() > 1 ? 'robots' : 'robot' ?></label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_players_unlocked() > 1 || mmrpg_prototype_battles_complete('dr-light') >= MMRPG_SETTINGS_CHAPTER1_MISSIONS): ?>
                    <span class="pipe">|</span>
                    <a class="link link_players" data-step="edit_players" data-index="<?= $this_menu_indexes['players'] ?>" data-source="frames/edit_players.php?action=players" data-music="misc/player-editor" data-maybe-tooltip="<?= $this_menu_tooltips['players'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-user-circle"></i>
                        <label><?= mmrpg_prototype_players_unlocked() > 1 ? 'players' : 'player' ?></label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_abilities_unlocked() >= 2): ?>
                    <span class="pipe">|</span>
                    <a class="link link_abilities" data-step="abilities" data-index="<?= $this_menu_indexes['abilities'] ?>" data-source="frames/abilities.php" data-music="misc/item-viewer" data-maybe-tooltip="<?= $this_menu_tooltips['abilities'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-fire-alt"></i>
                        <label>abilities</label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_items_unlocked() > 0): ?>
                    <span class="pipe">|</span>
                    <a class="link link_items" data-step="items" data-index="<?= $this_menu_indexes['items'] ?>" data-source="frames/items.php" data-music="misc/item-viewer" data-maybe-tooltip="<?= $this_menu_tooltips['items'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-briefcase"></i>
                        <label>items</label>
                    </a>
                <? endif; ?>
                <? if (mmrpg_prototype_stars_unlocked() > 0): ?>
                    <span class="pipe">|</span>
                    <a class="link link_stars" data-step="stars" data-index="<?= $this_menu_indexes['stars'] ?>" data-source="frames/starforce.php" data-music="misc/star-force" data-maybe-tooltip="<?= $this_menu_tooltips['stars'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <i class="fa fas fa-star"></i>
                        <label>stars</label>
                    </a>
                <? endif; ?>
                <span class="pipe">|</span>
                <a class="link link_data" data-step="database" data-index="<?= $this_menu_indexes['database'] ?>" data-source="frames/database.php" data-music="misc/data-base" data-maybe-tooltip="<?= $this_menu_tooltips['database'] ?>" data-tooltip-type="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                    <i class="fa fas fa-compact-disc"></i>
                    <label>database</label>
                </a>

            </div>
        </div>

    </div>

    <div class="menu select_this_player" data-step="1" data-title="Player Select (<?= !empty($_SESSION[$session_token]['DEMO']) || $unlock_count_players == 1 ? '1 Player' : $unlock_count_players.' Players' ?>)" data-select="this_player_token">
        <span class="header block_1 header_types type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <span class="count">Player Select (<?= !empty($_SESSION[$session_token]['DEMO']) || $unlock_count_players == 1 ? '1 Player' : $unlock_count_players.' Players' ?>)</span>
            <?/*<span class="reload">&#8634;</span>*/?>
        </span>
        <?
        // Require the prototype players display file
        require_once(MMRPG_CONFIG_ROOTDIR.'prototype/players.php');

        ?>
    </div>

    <div class="menu menu_hide select_this_battle" data-step="2" data-title="Battle Select" data-select="this_battle_token">
        <span class="header block_1 header_types type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <span class="count"><?= !empty($_SESSION[$session_token]['DEMO']) ? 'Mega Man RPG Prototype' : 'Mission Select' ?></span>
        </span>
        <?

        // Require the prototype campaign chapters display file
        require_once(MMRPG_CONFIG_ROOTDIR.'prototype/chapters.php');

        // If we're NOT in demo mode, maybe add a back button
        if (empty($_SESSION[$session_token]['DEMO'])){
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
        if (!empty($_SESSION[$session_token]['DEMO'])){

            // There is no demo get out of here!

        }
        /*
         * NORMAL ROBOT SELECT
         */
        elseif (mmrpg_prototype_robots_unlocked() > 1){

            // Print out the opening tags for the robot select container
            echo '<div class="menu menu_hide select_this_player_robots" data-step="3" data-limit="" data-title="Robot Select" data-select="this_player_robots">'."\n";
            echo '<span class="header block_1 header_types type_'.MMRPG_SETTINGS_CURRENT_FIELDTYPE.'"><span class="count">Robot Select</span></span>'."\n";

            // Require the prototype robots display file
            require_once(MMRPG_CONFIG_ROOTDIR.'prototype/robots.php');

            // Print out the back button for going back to player select
            echo '<a class="option option_back block_1" data-back="2">&#9668; Back</a>'."\n";

            // Print out the closing tags for the robot select container
            echo '</div>'."\n";

        }
    ?>

    <div class="menu menu_hide menu_file_save" data-step="file_save" data-source="frames/settings.php"></div>

    <div class="menu menu_hide menu_items" data-step="items" data-source="frames/items.php"></div>

    <div class="menu menu_hide menu_abilites" data-step="abilities" data-source="frames/abilities.php"></div>

    <div class="menu menu_hide menu_shop" data-step="shop" data-source="frames/shop.php"></div>

    <div class="menu menu_hide menu_edit_robots" data-step="edit_robots" data-source="frames/edit_robots.php?action=robots"></div>

    <div class="menu menu_hide menu_edit_players" data-step="edit_players" data-source="frames/edit_players.php?action=players"></div>

    <div class="menu menu_hide menu_leaderboard" data-step="leaderboard" data-source="frames/leaderboard.php"></div>

    <div class="menu menu_hide menu_database" data-step="database" data-source="frames/database.php"></div>

    <div class="menu menu_hide menu_stars" data-step="stars" data-source="frames/starforce.php"></div>

    <div class="menu menu_hide menu_help" data-step="help" data-source="frames/help.php"></div>

    <div class="menu menu_hide menu_loading" data-step="loading" style="min-height: 480px;">
        <div class="option_wrapper option_wrapper_noscroll" style="color: white; font-weight: bold; line-height: 150px; letter-spacing: 4px; opacity: 0.75; margin-right: 0; background-color: rgba(0, 0, 0, 0.10); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; overflow: hidden; min-height: 475px; ">
            <div style="line-height: 40px; margin-top: 50px;">
                <span class="sprite sprite_40x40 sprite_40x40_left_00 " style="display: inline-block; position: static; background-image: url(images/assets/robot-loader_mega-man.gif); ">&nbsp;</span><br />
                <span class="label" style="display: inline-block;">loading</span>
            </div>
        </div>
    </div>

</div>

<div id="falloff" class="falloff_bottom"></div>
<?
//debug_profiler_checkpoint('after-html-body');

?>
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/ready-room.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">

// Update relevent game settings and flags
<? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
gameSettings.fadeIn = <?= isset($_GET['flag_skip_fadein']) && $_GET['flag_skip_fadein'] == 'true' ? 'false' : 'true' ?>;
gameSettings.demo = false;
gameSettings.passwordUnlocked = 0;
gameSettings.startLink = '<?= $prototype_start_link ?>';
gameSettings.windowEventsCanvas = [];
gameSettings.windowEventsMessages = [];
gameSettings.totalMissionsComplete = <?= $total_missions_complete ?>;
gameSettings.totalPlayerOptions = <?= $total_player_options ?>;
gameSettings.totalRobotOptions = <?= $total_robot_options ?>;
gameSettings.prototypeBannerKey = 0;
gameSettings.prototypeBanners = ['<?= $prototype_banner_image ?>'];
gameSettings.readyRoomUnlocked = <?= $ready_room_unlocked ? 'true' : 'false' ?>;
<?

// Define any menu frames already seen so know what's new
$menu_frames_seen = !empty($_SESSION[$session_token]['battle_settings']['menu_frames_seen']) ? $_SESSION[$session_token]['battle_settings']['menu_frames_seen'] : 'home';
$menu_frames_seen = strstr($menu_frames_seen, '|') ? explode('|', $menu_frames_seen) : array($menu_frames_seen);
echo('gameSettings.menuFramesSeen = '.json_encode($menu_frames_seen).';'.PHP_EOL);

// Load all the Ready Room details if allowed, otherwise define them as empty
//debug_profiler_checkpoint('before-ready-room');
if ($ready_room_unlocked){

    // Generate a JSON array of all currently unlocked player players w/ basic data for prototype menu reference
    $include_extra = array();
    if (mmrpg_prototype_item_unlocked('kalinka-link')){ $include_extra['kalinka'] = array('player_token' => 'kalinka', 'current_player' => 'dr-cossack'); }
    $this_unlocked_players_index = mmrpg_prototype_players_unlocked_index_json($include_extra);
    //error_log('$include_extra ='.print_r($include_extra, true));
    //error_log('$this_unlocked_players_index ='.print_r($this_unlocked_players_index, true));
    //error_log('$this_unlocked_players_index(A) ='.print_r(array_keys($this_unlocked_players_index), true));

    // Generate a JSON array of all currently unlocked player robots w/ basic data for prototype menu reference
    $include_extra = array();
    if (mmrpg_prototype_item_unlocked('auto-link')){ $include_extra['auto'] = array('robot_token' => 'auto', 'robot_image_size' => 80, 'current_player' => 'dr-light'); }
    if (mmrpg_prototype_item_unlocked('reggae-link')){ $include_extra['reggae'] = array('robot_token' => 'reggae', 'current_player' => 'dr-wily'); }
    $this_unlocked_robots_index = mmrpg_prototype_robots_unlocked_index_json($include_extra);
    //error_log('$this_unlocked_robots_index ='.print_r($this_unlocked_robots_index, true));
    //error_log('$this_unlocked_robots_index ='.print_r(array_keys($this_unlocked_robots_index), true));

    // Check to see if we need to display entrance animations for any recently unlocked players
    $players_pending_entrance_animations = rpg_prototype::get_players_pending_entrance_animations();
    //error_log('$players_pending_entrance_animations = '.print_r($players_pending_entrance_animations, true));
    if (!empty($players_pending_entrance_animations)){
        foreach ($players_pending_entrance_animations AS $player_key => $player_token){
            if (!isset($this_unlocked_players_index[$player_token])){ continue; }
            $new_player_data = $this_unlocked_players_index[$player_token];
            //error_log('add entrance animation for '.$player_token);
            if (!isset($new_player_data['flags'])){ $new_player_data['flags'] = array(); }
            $new_player_data['flags'][] = 'is_newly_unlocked';
            //error_log('$new_player_data = '.print_r($new_player_data, true));
            $this_unlocked_players_index[$player_token] = $new_player_data;
        }
        rpg_prototype::clear_players_pending_entrance_animations();
    }

    // Check to see if we need to display entrance animations for any recently unlocked robots
    $robots_pending_entrance_animations = rpg_prototype::get_robots_pending_entrance_animations();
    //error_log('$robots_pending_entrance_animations = '.print_r($robots_pending_entrance_animations, true));
    if (!empty($robots_pending_entrance_animations)){
        foreach ($robots_pending_entrance_animations AS $robot_key => $robot_token){
            if (!isset($this_unlocked_robots_index[$robot_token])){ continue; }
            $new_robot_data = $this_unlocked_robots_index[$robot_token];
            //error_log('add entrance animation for '.$robot_token);
            if (!isset($new_robot_data['flags'])){ $new_robot_data['flags'] = array(); }
            $new_robot_data['flags'][] = 'is_newly_unlocked';
            //error_log('$new_robot_data = '.print_r($new_robot_data, true));
            $this_unlocked_robots_index[$robot_token] = $new_robot_data;
        }
        rpg_prototype::clear_robots_pending_entrance_animations();
    }

    // Remove any players or robots that are currently locked in endless attack mode
    $endless_attack_savedata = mmrpg_prototype_get_endless_sessions();
    if (!empty($endless_attack_savedata)){
        foreach ($endless_attack_savedata AS $player => $savedata){
            if (isset($this_unlocked_players_index[$player])){ unset($this_unlocked_players_index[$player]); }
            foreach ($savedata['robots'] AS $robot){ if (isset($this_unlocked_robots_index[$robot])){ unset($this_unlocked_robots_index[$robot]); } }
        }
    }

    // Print out the generated indexes for use in the game settings
    echo 'gameSettings.customIndex.unlockedPlayersIndex = '.json_encode($this_unlocked_players_index).';'.PHP_EOL;
    echo 'gameSettings.customIndex.unlockedRobotsIndex = '.json_encode($this_unlocked_robots_index).';'.PHP_EOL;

} else {

    // Print out empty indexes to prevent issues in the game settings
    echo 'gameSettings.customIndex.unlockedPlayersIndex = {};'.PHP_EOL;
    echo 'gameSettings.customIndex.unlockedRobotsIndex = {};'.PHP_EOL;

}
//debug_profiler_checkpoint('after-ready-room');

?>

// Define any preset menu selections
battleOptions['this_user_id'] = <?= $this_userid ?>;
<? if (!empty($_SESSION[$session_token]['battle_settings']['this_player_token'])){ ?>
    battleOptions['this_player_id'] = <?= $mmrpg_index_players[$_SESSION[$session_token]['battle_settings']['this_player_token']]['player_id'] ?>;
    battleOptions['this_player_token'] = '<?= $_SESSION[$session_token]['battle_settings']['this_player_token'] ?>';
<? } elseif($unlock_count_players < 2){ ?>
    battleOptions['this_player_id'] = <?= $mmrpg_index_players['dr-light']['player_id'] ?>;
    battleOptions['this_player_token'] = 'dr-light';
<? } ?>
<? if ($unlock_count_players === 1 && mmrpg_prototype_robots_unlocked() === 1){ ?>
    battleOptions['this_player_robots'] = ['101_mega-man'];
<? } ?>

// Create the document ready events
$(document).ready(function(){

    // Make sure the music button is in the appropriate place
    top.mmrpg_music_context('home');

    // Define the type of music we'll be using and autoplay it
    <?
    //debug_profiler_checkpoint('before-music-calc');
    $autoplay_music = 'misc/player-select';
    if ($prototype_start_link === 'home'
        && !empty($_SESSION[$session_token]['battle_settings']['this_player_token'])){
        $current_player = $_SESSION[$session_token]['battle_settings']['this_player_token'];
        //$autoplay_music = mmrpg_prototype_get_player_mission_music($current_player, $session_token);
        $current_chapter = mmrpg_prototype_player_currently_selected_chapter($current_player);
        $autoplay_music = mmrpg_prototype_get_chapter_music($current_player, $current_chapter, $session_token);
        if (empty($autoplay_music)){ $autoplay_music = 'misc/stage-select-'.$current_player; }
    }
    //debug_profiler_checkpoint('after-music-calc');
    ?>
    parent.mmrpg_music_load('<?= $autoplay_music ?>', true, false);

    <? if (rpg_game::is_user()){ ?>
        // The user is logged-in so let's keep the session alive
        mmrpg_keep_session_alive(<?= rpg_game::get_userid() ?>);
    <? } ?>

    // Make sure we turn off any temp-UI modifications
    mmrpg_toggle_screenshot_mode(false);
    parent.mmrpg_toggle_screenshot_mode(false);

});
</script>
<?

// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');

?>
</body>
</html>
<?

// Unset the database variable
unset($db);
//debug_profiler_checkpoint('after-everything');
//require('admin/includes/debug_profiler_bottom.php');

?>