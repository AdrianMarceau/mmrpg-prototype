<?php

// MAINTENANCE
if (MMRPG_CONFIG_MAINTENANCE_MODE && !in_array($_SERVER['REMOTE_ADDR'], array('99.226.253.166', '127.0.0.1', '99.226.238.61', '72.137.208.122'))){
    die('<div style="font-family: Arial; font-size: 16px; line-height: 21px; margin: 0; padding: 20px 25%; background-color: rgb(0, 122, 0); color: #FFFFFF; text-align: left; border-bottom: 1px solid #090909;">
        UPDATE IN PROGRESS<br /> The Mega Man RPG Prototype is currently being updated.  Please stand by until further notice.  Several parts of the website are being taken offline during this process and any progress made during will likely be lost, so please hold tight before trying to log in again.  I apologize for the inconvenience and thank you for your patience.<br /> - Adrian
        </div>');
}

// Include the TOP file
require_once('top.php');

// DEBUG DEBUG DEBUG
//if ($_SERVER['REMOTE_ADDR'] != '99.255.218.123'){ die('Currently down for maintenance.  Please check back later.'); }

//die('<pre>'.print_r($_GET, true).'</pre>');

// Define the default SEO and MARKUP variables
$this_seo_title = 'Prototype | Mega Man RPG Prototype | Last Updated '.preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE);
$this_seo_keywords = 'megaman,mega man,protoman,proto man,bass,rpg,prototype,dr.light,dr.wily,dr.cossack,battle,browser,pbbg,ipad,firefox,chrome,safari';
$this_seo_description = 'Battle through more than thirty robot masters in classic RPG style with either Dr. Light and Mega Man, Dr. Wily and Bass, or Dr. Cossack and Proto Man! The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the default Open Graph tag variables
$this_graph_data = array(
    'title' => 'Mega Man RPG Prototype',
    'type' => 'website',
    'url' => $this_current_url,
    'image' => MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE,
    'site_name' => 'Mega Man RPG Prototype',
    'description' => $this_seo_description,
    );

// If a reset was intentionally called
if (!empty($_GET['reset']) || (!empty($_SESSION['GAME']['DEMO']) && !empty($_SESSION['GAME']['CACHE_DATE']) && $_SESSION['GAME']['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE)){
    // Reset the game session
    mmrpg_reset_game_session($this_save_filepath);
}
// Else if this is an out-of-sync demo
elseif (!empty($_SESSION['GAME']['DEMO']) && !empty($_SESSION['GAME']['CACHE_DATE']) && $_SESSION['GAME']['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE){
    // Reset the game session
    mmrpg_reset_game_session($this_save_filepath);
}
// Check if the session has not been created or the cache date has changed
elseif (
    !empty($_GET['reload']) || // if a reload was specifically requested
    !isset($_SESSION['GAME']['CACHE_DATE']) || // if there is no session created yet
    (!empty($_SESSION['GAME']['DEMO']) && $_SESSION['GAME']['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE) // if we're in demo mode and the cache date is out of sync
    ){

    // Ensure there is a save file to load
    if (!empty($this_save_filepath) && file_exists($this_save_filepath)){
        // Load the save file into memory and overwrite the session
        mmrpg_load_game_session($this_save_filepath);
    }
    // Otherwise, simply reset the game
    else {
        // Reset the game session
        mmrpg_reset_game_session($this_save_filepath);
    }

    // Update the cache date to reflect the reload
    $_SESSION['GAME']['CACHE_DATE'] = MMRPG_CONFIG_CACHE_DATE;
    // Save the updated file back to the system
    mmrpg_save_game_session($this_save_filepath);

}
// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();


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

?>
<!DOCTYPE html>
<html<?/* not-manifest="manifest.php?<?=MMRPG_CONFIG_CACHE_DATE?>" */?> lang="en" xmlns:og="http://opengraphprotocol.org/schema/">
<head>
<meta charset="UTF-8" />
<title><?= $this_seo_title ?></title>
<meta name="keywords" content="<?= $this_seo_keywords ?>" />
<meta name="description" content="<?= $this_seo_description ?>" />
<meta name="robots" content="index,follow,noodp" />
<link rel="sitemap" type="application/xml" title="Sitemap" href="<?= MMRPG_CONFIG_ROOTURL ?>sitemap.xml" />
<meta name="format-detection" content="telephone=no" />
<base href="<?= MMRPG_CONFIG_ROOTURL ?>">
<? foreach ($this_graph_data AS $token => $value){ echo '<meta property="og:'.str_replace('__', ':', $token).'" content="'.$value.'"/>'."\n"; } ?>
<link rel="browser-game-info" href="<?= MMRPG_CONFIG_ROOTURL ?>mmrpg-info.xml" />
<link rel="shortcut icon" type="image/x-icon" href="<?= MMRPG_CONFIG_ROOTURL ?>images/assets/favicon<?= !MMRPG_CONFIG_IS_LIVE ? '-local' : '' ?>.ico">
<link type="text/css" href="<?= MMRPG_CONFIG_ROOTURL ?>styles/reset.css" rel="stylesheet" />
<link type="text/css" href="<?= MMRPG_CONFIG_ROOTURL ?>styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<meta name="viewport" content="user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, width=768, height=1004">
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="format-detection" content="telephone=no">
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<link rel="apple-touch-icon" sizes="72x72" href="<?= MMRPG_CONFIG_ROOTURL ?>images/assets/ipad-icon_72x72.png" />
<link rel="apple-touch-startup-image" href="<?= MMRPG_CONFIG_ROOTURL ?>images/assets/ipad-startup_768x1004_portrait.png?<?=MMRPG_CONFIG_CACHE_DATE?>" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)" />
<link rel="apple-touch-startup-image" href="<?= MMRPG_CONFIG_ROOTURL ?>images/assets/ipad-startup_748x1024_landscape.png?<?=MMRPG_CONFIG_CACHE_DATE?>" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)" />
<link type="text/css" href="<?= MMRPG_CONFIG_ROOTURL ?>styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?elseif($flag_iphone):?>
<meta name="viewport" content="user-scalable=yes, width=768, height=1004">
<link type="text/css" href="<?= MMRPG_CONFIG_ROOTURL ?>styles/style-mobile-iphone.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<? $temp_window_flag = !empty($_SESSION['GAME']['index_settings']['windowFlag']) ? $_SESSION['GAME']['index_settings']['windowFlag'] : false; ?>
<body id="mmrpg" class="index <?= !empty($temp_window_flag) ? 'windowFlag_'.$temp_window_flag : '' ?> <?= $this_current_sub == 'facebook' ? 'windowFlag_facebookFrame' : '' ?>">
<?/*
<div style="margin: 0; padding: 10px 25%; background-color: rgb(122, 0, 0); color: #FFFFFF; text-align: left; border-bottom: 1px solid #090909;">
ATTENTION!<br /> The Mega Man RPG Prototype will be updating very soon.  Please, please log off from your accounts as soon as possible and stand by until further notice.  Several parts of the website will be taken offline during this process and any progress made during or directly before will likely be lost.  Thank you and look forward to lots of new stuff!<br /> - Adrian
</div>
*/?>
<h1 id="header">Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></h1>
<div id="window" style="position: relative; ">

    <?if($this_online_flag && $this_browser_flag):?>
        <?if(!$flag_wap):?>
            <iframe class="loading" name="battle" src="<?= MMRPG_CONFIG_ROOTURL ?>prototype.php?wap=false" width="768" height="1004" frameborder="1" scrolling="no"></iframe>
        <?else:?>
            <iframe class="loading" name="battle" src="<?= MMRPG_CONFIG_ROOTURL ?>prototype.php?wap=true" width="768" height="748" frameborder="0" scrolling="no"></iframe>
        <?endif;?>
        <div id="music" class="onload">
            <a class="toggle paused" href="#" onclick=""><span><span>loading&hellip;</span></span></a>
            <audio class="stream paused" onended="this.play();">
                <div style="color: white; background-color: black; padding: 10px;">Your browser does not support the audio tag.</div>
            </audio>
        </div>
        <div id="events" class="hidden">
            <div class="event_wrapper">
                <div class="event_container">
                    <div id="canvas" class="event_canvas"></div>
                    <div id="messages" class="event_messages"></div>
                    <div id="buttons" class="event_buttons"><a class="event_continue">Continue</a></div>
                </div>
            </div>
        </div>
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
<?if(!$flag_wap):?>
<div id="credits">
    <a href="<?= MMRPG_CONFIG_ROOTURL ?>">&laquo; Back to Website</a> |
    Mega Man and all related names and characters are &copy; <a href="http://www.capcom.com/" target="_blank" rel="nofollow">Capcom</a> 1986 - <?= date('Y') ?>.
    | <a href="<?= MMRPG_CONFIG_ROOTURL ?>contact/">Contact &amp; Feedback &raquo;</a><?= !$flag_iphone ? '<br />' : '' ?>
    This game is fan-made by <a href="https://plus.google.com/113336469005774860291?rel=author" target="_blank">Adrian Marceau</a>, not affiliated or endorsed by Capcom at all, and is in no way official. Any and all <a href="contact/" target="_blank">feedback</a> is appreciated. :)
</div>
<?endif;?>
<script type="text/javascript" src="<?= MMRPG_CONFIG_ROOTURL ?>scripts/jquery.js"></script>
<script type="text/javascript" src="<?= MMRPG_CONFIG_ROOTURL ?>scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Define the key client variables
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
</script>
<script type="text/javascript">
// When the document is ready for event binding
$(document).ready(function(){
    // Preload essential audio tracks
    mmrpg_music_preload('misc/player-select');
    mmrpg_music_preload('misc/stage-select-dr-light');
    //mmrpg_music_preload('misc/stage-select-dr-wily');
    //mmrpg_music_preload('misc/data-base');
    //mmrpg_music_preload('misc/leader-board');
    //mmrpg_music_preload('misc/file-menu');
    //mmrpg_music_preload('misc/robot-editor');
    // Check if we're running the game in mobile mode
    if (false && gameSettings.wapFlag){
        // Let the user know about the full-screen option for mobile browsers
        if (('standalone' in window.navigator) && !window.navigator.standalone){
            //alert('launched from full-screen ready browser, but not in full screen...');
            alert('Use the "Add to Home Screen" option for fullscreen view! :)');
            } else if (('standalone' in window.navigator) && window.navigator.standalone){
            //alert('launched from full-screen ready browser, and in full screen!');
            } else {
            //alert('launched from a regular old browser...');
            }
        }
    // Collect a reference to the continue button
    var eventContinue = $('#events #buttons .event_continue');
    // Create the continue event for the event window
    eventContinue.click(function(e){
        e.preventDefault();
        //alert('clicked');
        windowEventDestroy();
        if (gameSettings.canvasMarkupArray.length || gameSettings.messagesMarkupArray.length){
            windowEventDisplay();
            }
        });

});
//function updateMobileCache(event){ window.applicationCache.swapCache(); }
// Define a function for displaying event messages to the player
gameSettings.canvasMarkupArray = [];
gameSettings.messagesMarkupArray = [];
function windowEventCreate(canvasMarkupArray, messagesMarkupArray){
    //console.log('windowEventCreate('+canvasMarkupArray+', '+messagesMarkupArray+')');
    gameSettings.canvasMarkupArray = canvasMarkupArray;
    gameSettings.messagesMarkupArray = messagesMarkupArray;
    windowEventDisplay();
}
// Define a function for displaying event messages to the player
function windowEventDisplay(){
    var eventContainer = $('#events');
    //console.log('windowEventDisplay()');
    var canvasMarkup = gameSettings.canvasMarkupArray.length ? gameSettings.canvasMarkupArray.shift() : '';
    var messagesMarkup = gameSettings.messagesMarkupArray.length ? gameSettings.messagesMarkupArray.shift() : '';
    $('#canvas', eventContainer).empty().html(canvasMarkup);
    $('#messages', eventContainer).empty().html(messagesMarkup);
    eventContainer.css({opacity:0}).removeClass('hidden').animate({opacity:1},300,'swing');
    $(window).focus();
    //alert(eventMarkup);
}
// Define a function for displaying event messages to the player
function windowEventDestroy(){
    var eventContainer = $('#events');
    //console.log('windowEventDestroy()');
    $('#canvas', eventContainer).empty();
    $('#messages', eventContainer).empty();
    eventContainer.addClass('hidden');
    //alert(eventMarkup);
}
</script>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');
?>
</body>
</html>