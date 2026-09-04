<?php

// MAINTENANCE
if (MMRPG_CONFIG_MAINTENANCE_MODE && !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '123.456.789.012'))){
    die('<div style="font-family: Arial; font-size: 16px; line-height: 21px; margin: 0; padding: 20px 25%; background-color: rgb(0, 122, 0); color: #FFFFFF; text-align: left; border-bottom: 1px solid #090909;">
        UPDATE IN PROGRESS<br /> The Mega Man RPG Prototype is currently being updated.  Please stand by until further notice.  Several parts of the website are being taken offline during this process and any progress made during will likely be lost, so please hold tight before trying to log in again.  I apologize for the inconvenience and thank you for your patience.<br /> - Adrian
        </div>');
}

// Include the TOP file
require_once('top.php');
$GAME_SESSION = $_SESSION['GAME'];
session_write_close();

// Set a time limit for game scripts to prevent overdoing it
if (defined('MMRPG_CONFIG_IS_LIVE') && MMRPG_CONFIG_IS_LIVE === false){ set_time_limit(5); }

// If the user is not logged in, don't allow them here
if (!rpg_game::is_user()){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'file/load/');
    exit();
}

// DEBUG DEBUG DEBUG
//if ($_SERVER['REMOTE_ADDR'] != '99.255.218.123'){ die('Currently down for maintenance.  Please check back later.'); }

//die('<pre>'.print_r($_GET, true).'</pre>');

// Define the default SEO and MARKUP variables
$this_seo_title = 'Prototype | Mega Man RPG Prototype';
$this_seo_keywords = 'megaman,mega man,protoman,proto man,bass,rpg,prototype,dr.light,dr.wily,dr.cossack,battle,browser,pbbg,ipad,firefox,chrome,safari';
$this_seo_description = 'Battle through more than 100 robot masters in classic RPG style with either Dr. Light and Mega Man, Dr. Wily and Bass, or Dr. Cossack and Proto Man! The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster. Last updated '.mmrpg_print_cache_date().'.';

// Define the default Open Graph tag variables
$this_graph_data = array(
    'title' => 'Mega Man RPG Prototype',
    'type' => 'website',
    'url' => $this_current_url,
    'image' => MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo-2k19.png?'.MMRPG_CONFIG_CACHE_DATE,
    'site_name' => 'Mega Man RPG Prototype',
    'description' => $this_seo_description,
    );

// If a reset was intentionally called
if (!empty($_GET['reset']) || (!empty($GAME_SESSION['DEMO']) && !empty($GAME_SESSION['CACHE_DATE']) && $GAME_SESSION['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE)){
    // Reset the game session
    mmrpg_reset_game_session();
}
// Else if this is an out-of-sync demo
elseif (!empty($GAME_SESSION['DEMO']) && !empty($GAME_SESSION['CACHE_DATE']) && $GAME_SESSION['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE){

    // Reset the game session
    mmrpg_reset_game_session();

}
// Check if the session has not been created or the cache date has changed
elseif (
    !empty($_GET['reload']) || // if a reload was specifically requested
    !isset($GAME_SESSION['CACHE_DATE']) || // if there is no session created yet
    (!empty($GAME_SESSION['DEMO']) && $GAME_SESSION['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE) // if we're in demo mode and the cache date is out of sync
    ){

    // Ensure there is a save file to load
    if (!rpg_user::is_guest()){

        // Load the save file into memory and overwrite the session
        mmrpg_load_game_session();

    }
    // Otherwise, simply reset the game
    else {

        // Reset the game session
        mmrpg_reset_game_session();
    }

    // Update the cache date to reflect the reload
    $GAME_SESSION['CACHE_DATE'] = MMRPG_CONFIG_CACHE_DATE;

    // Save the updated file back to the system
    mmrpg_save_game_session();

}
// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['SKILLS'] = array();

// If the player has unlocked more than one playable character,
// we should clear the player selection so they see the title screen
if (mmrpg_prototype_players_unlocked() > 1){ unset($GAME_SESSION['battle_settings']['this_player_token']); }

// Check to see if a window flag has been set in the session already
$index_window_flag = !empty($GAME_SESSION['index_settings']['windowFlag']) ? $GAME_SESSION['index_settings']['windowFlag'] : false;

// Define the flag that toggles the game's online/offline status
$this_online_flag = true;
$this_browser_flag = true;
//if ($_SERVER['REMOTE_ADDR'] == '99.255.218.123'){ $this_online_flag = true; }
preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
if (count($matches)>1){
    //Then we're using IE
    $version = $matches[1];
    switch(true){
        case ($version<=8):
            //IE 8 or under!
            $this_browser_flag = false;
            break;
        case ($version==9):
            //IE9!
            break;
        default:
            //You get the idea
    }
}

// Close the session now that we're done writing
session_write_close();

?>
<!DOCTYPE html>
<html lang="en" xmlns:og="http://opengraphprotocol.org/schema/" data-index="game">
<head>

<meta charset="UTF-8" />

<title><?= $this_seo_title ?></title>

<meta name="keywords" content="<?= $this_seo_keywords ?>" />
<meta name="description" content="<?= $this_seo_description ?>" />
<? if (MMRPG_CONFIG_IS_LIVE === true && MMRPG_CONFIG_SERVER_ENV === 'prod'){ ?>
    <meta name="robots" content="index,follow,noodp" />
<? } else { ?>
    <meta name="robots" content="noindex,nofollow,noodp" />
<? } ?>
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />

<base href="<?= MMRPG_CONFIG_ROOTURL ?>">

<link rel="sitemap" type="application/xml" title="Sitemap" href="<?= MMRPG_CONFIG_ROOTURL ?>sitemap.xml" />

<? if(!defined('MMRPG_CRITICAL_ERROR')){  foreach ($this_graph_data AS $token => $value){ echo '<meta property="og:'.str_replace('__', ':', $token).'" content="'.$value.'"/>'."\n"; } } ?>

<link rel="browser-game-info" href="<?= MMRPG_CONFIG_ROOTURL ?>mmrpg-info.xml" />

<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">

<link type="text/css" href="styles/reset.css" rel="stylesheet" />

<style type="text/css"> html, body { background-color: #262626; } </style>

<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />

<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />

<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/events.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />

<link rel="apple-touch-icon" sizes="72x72" href="images/assets/ipad-icon-2k19_72x72.png" />
<? /* <meta name="viewport" content="user-scalable=yes, width=768, height=1004"> */ ?>
<meta name="viewport" content="user-scalable=yes, width=device-width, min-width=768, initial-scale=1">

</head>
<body id="mmrpg" class="index <?= !empty($index_window_flag) ? 'windowFlag_'.$index_window_flag : '' ?> <?= $this_current_sub == 'facebook' ? 'windowFlag_facebookFrame' : '' ?>">

<h1 id="header">Mega Man RPG Prototype | Last Updated <?= mmrpg_print_cache_date() ?></h1>
<div id="window" style="position: relative; ">

    <?if($this_online_flag && $this_browser_flag):?>
        <?if(!$flag_wap):?>
            <iframe class="loading" name="prototype" src="<?= MMRPG_CONFIG_ROOTURL ?>prototype.php?wap=false" width="768" height="1004" frameborder="1" scrolling="no"></iframe>
        <?else:?>
            <iframe class="loading" name="prototype" src="<?= MMRPG_CONFIG_ROOTURL ?>prototype.php?wap=true" width="768" height="748" frameborder="0" scrolling="no"></iframe>
        <?endif;?>
        <div id="music" class="onload">
            <span class="btn-fx fx-1"></span><span class="btn-fx fx-2"></span>
            <a class="toggle paused has_pixels" href="#" onclick=""><span><span>loading&hellip;</span></span></a>
            <input type="hidden" class="audio-stream music paused" />
            <input type="hidden" class="audio-stream effects paused" />
        </div>
        <? /*
        <div id="events" class="hidden">
            <div class="event_wrapper">
                <div class="event_container">
                    <div id="canvas" class="event_canvas"></div>
                    <div id="messages" class="event_messages"></div>
                    <div id="buttons" class="event_buttons"><a class="event_continue">Continue</a></div>
                </div>
            </div>
        </div>
        */ ?>
    <?elseif(!$this_online_flag):?>
        <strong style="display: block; margin: 100px auto; font-size: 13px; line-height: 19px; color: #DEDEDE; ">
            The <strong>Mega Man RPG Prototype</strong> is temporarily down for maintenance.<br />
            Please check back later and we aplogize for the inconvenience.
        </strong>
    <?elseif(!$this_browser_flag):?>
        <strong style="display: block; margin: 100px auto; font-size: 13px; line-height: 19px; color: #DEDEDE; ">
            The <strong>Mega Man RPG Prototype</strong> is not supported by your browser.<br />
            Please try upgrading your version or consider switching to something else.<br />
            We aplogize for the inconvenience.
        </strong>
    <?endif;?>

</div>
<?
// Require the common footer
$footer_context = 'base';
require(MMRPG_CONFIG_ROOTDIR.'includes/footer.php');
?>
<div id="winmods">
    <fieldset class="field width" style="display: none;">
        <legend class="label" alt="Adjust Window Width">
            <i class="fa fas fa-tablet-alt" style="position: relative; bottom: 4px;"></i>
            <i class="fa fas fa-arrows-alt-h" style="position: absolute; bottom: -4px; left: 50%; transform: translate(-50%, 0);"></i>
        </legend>
        <a type="button" class="button" data-mod-name="width" data-mod-value="small">Small</a>
        <a type="button" class="button" data-mod-name="width" data-mod-value="large">Large</a>
        <a type="button" class="button" data-mod-name="width" data-mod-value="flex">Flex</a>
    </fieldset>
    <fieldset class="field height">
        <legend class="label" alt="Adjust Window Height">
            <i class="fa fas fa-tablet-alt"></i>
            <i class="fa fas fa-arrows-alt-v"></i>
        </legend>
        <a type="button" class="button" data-mod-name="height" data-mod-value="small">Small</a>
        <a type="button" class="button" data-mod-name="height" data-mod-value="large">Large</a>
        <a type="button" class="button" data-mod-name="height" data-mod-value="flex">Flex</a>
    </fieldset>
</div>
<? /*
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src=".libs/howler-js/howler.core.min.js"></script>
<script type="text/javascript" src=".libs/howler-js/howler.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
*/ ?>
<? require(MMRPG_CONFIG_ROOTDIR.'scripts/gamescripts.all.php'); ?>
<? require(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.all.php'); ?>
<script type="text/javascript">

// When the document is ready for event binding
$(document).ready(function(){

    // Preload essential audio tracks
    mmrpg_music_preload('misc/sound-effects-curated');
    mmrpg_music_preload('misc/player-select');
    mmrpg_preload_misc_image('images/assets/mmrpg-game-start_logo-base.png?20230813');
    mmrpg_preload_misc_image('images/assets/mmrpg-game-start_logo-full.png?20230813');

    // If window mod tools exist on the page, we should bind events to them
    var $gameWindow = $('#window');
    var $windowMods = $('#winmods');
    if ($gameWindow.length
        && $windowMods.length){

        // Load previous preferences if present
        // 2017-10-16: Disabling this feature going forward, the game has one sized window
        var previousWidth = 'small'; //localStorage.getItem('mmrpg-window-width') || 'small';
        var previousHeight = 'small'; //localStorage.getItem('mmrpg-window-height') || 'small';

        // Set previous preferences
        $gameWindow.attr('data-window-width', previousWidth);
        $gameWindow.attr('data-window-height', previousHeight);

        // Collect references to the different buttons
        var $widthButtons = $('.width .button', $windowMods);
        var $heightButtons = $('.height .button', $windowMods);

        // Highlight active buttons
        $widthButtons.removeClass('active').filter('[data-mod-value="' + previousWidth + '"]').addClass('active');
        $heightButtons.removeClass('active').filter('[data-mod-value="' + previousHeight + '"]').addClass('active');

        // Width functionality
        $widthButtons.bind('click', function() {
            var value = $(this).data('mod-value');
            $gameWindow.attr('data-window-width', value);
            localStorage.setItem('mmrpg-window-width', value);
            $widthButtons.removeClass('active');
            $(this).addClass('active');
            });

        // Height functionality
        $heightButtons.bind('click', function() {
            var value = $(this).data('mod-value');
            $gameWindow.attr('data-window-height', value);
            localStorage.setItem('mmrpg-window-height', value);
            $heightButtons.removeClass('active');
            $(this).addClass('active');
            });
    }

});


// Add some functionality for a "cinema mode" where it fades credits and stuff
(function(){
    var $body = $('body');
    var $gameWindow = $('#window')
    // When the parent window loses focus, we'll assume the user is playing
    $(window).bind('blur', function(){
        //console.log('Window lost focus, user must be playing');
        $body.attr('data-cinema-mode', 'true');
        });
    // When the document is clicked outside the game window, we'll assume the user is configuring
    $(document).bind('click', function(event) {
        if ($(event.target).closest('#window').length){ return; }
        if ($(event.target).closest('#music').length){ return; }
        if ($(event.target).closest('#events').length){ return; }
        //console.log('Clicked outside game window, user must be configuring');
        $body.attr('data-cinema-mode', 'false');
        });
    // When the START button is clicked (disgused music button) we can start cinema mode
    $('#music').bind('click', function(e){
        //console.log('User clicked start, user must be playing');
        $body.attr('data-cinema-mode', 'true');
        e.stopPropagation();
        });
})();

</script>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');
?>
</body>
</html>