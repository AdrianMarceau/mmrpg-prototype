<?php

// MAINTENANCE
if (MMRPG_CONFIG_MAINTENANCE_MODE && !in_array($_SERVER['REMOTE_ADDR'], array('99.226.253.166', '127.0.0.1', '99.226.238.61', '72.137.208.122'))){
    die('<div style="font-family: Arial; font-size: 16px; line-height: 21px; margin: 0; padding: 20px 25%; background-color: rgb(0, 122, 0); color: #FFFFFF; text-align: left; border-bottom: 1px solid #090909;">
        UPDATE IN PROGRESS<br /> The Mega Man RPG Prototype is currently being updated.  Please stand by until further notice.  Several parts of the website are being taken offline during this process and any progress made during will likely be lost, so please hold tight before trying to log in again.  I apologize for the inconvenience and thank you for your patience.<br /> - Adrian
        </div>');
}

// Include the TOP file
require_once('top.php');

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
$this_seo_title = 'Prototype | Mega Man RPG Prototype | Last Updated '.mmrpg_print_cache_date();
$this_seo_keywords = 'megaman,mega man,protoman,proto man,bass,rpg,prototype,dr.light,dr.wily,dr.cossack,battle,browser,pbbg,ipad,firefox,chrome,safari';
$this_seo_description = 'Battle through more than thirty robot masters in classic RPG style with either Dr. Light and Mega Man, Dr. Wily and Bass, or Dr. Cossack and Proto Man! The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

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
if (!empty($_GET['reset']) || (!empty($_SESSION['GAME']['DEMO']) && !empty($_SESSION['GAME']['CACHE_DATE']) && $_SESSION['GAME']['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE)){
    // Reset the game session
    mmrpg_reset_game_session();
}
// Else if this is an out-of-sync demo
elseif (!empty($_SESSION['GAME']['DEMO']) && !empty($_SESSION['GAME']['CACHE_DATE']) && $_SESSION['GAME']['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE){

    // Reset the game session
    mmrpg_reset_game_session();

}
// Check if the session has not been created or the cache date has changed
elseif (
    !empty($_GET['reload']) || // if a reload was specifically requested
    !isset($_SESSION['GAME']['CACHE_DATE']) || // if there is no session created yet
    (!empty($_SESSION['GAME']['DEMO']) && $_SESSION['GAME']['CACHE_DATE'] != MMRPG_CONFIG_CACHE_DATE) // if we're in demo mode and the cache date is out of sync
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
    $_SESSION['GAME']['CACHE_DATE'] = MMRPG_CONFIG_CACHE_DATE;

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
<link type="text/css" href="styles/prototype-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />

<meta name="format-detection" content="telephone=no" />
<link rel="apple-touch-icon" sizes="72x72" href="images/assets/ipad-icon-2k19_72x72.png" />
<? /* <meta name="viewport" content="user-scalable=yes, width=768, height=1004"> */ ?>
<meta name="viewport" content="user-scalable=yes, width=device-width, min-width=768, initial-scale=1">

</head>
<? $temp_window_flag = !empty($_SESSION['GAME']['index_settings']['windowFlag']) ? $_SESSION['GAME']['index_settings']['windowFlag'] : false; ?>
<body id="mmrpg" class="index <?= !empty($temp_window_flag) ? 'windowFlag_'.$temp_window_flag : '' ?> <?= $this_current_sub == 'facebook' ? 'windowFlag_facebookFrame' : '' ?>">

<h1 id="header">Mega Man RPG Prototype | Last Updated <?= mmrpg_print_cache_date() ?></h1>
<div id="window" style="position: relative; ">

    <?if($this_online_flag && $this_browser_flag):?>
        <?if(!$flag_wap):?>
            <iframe class="loading" name="prototype" src="<?= MMRPG_CONFIG_ROOTURL ?>prototype.php?wap=false" width="768" height="1004" frameborder="1" scrolling="no"></iframe>
        <?else:?>
            <iframe class="loading" name="prototype" src="<?= MMRPG_CONFIG_ROOTURL ?>prototype.php?wap=true" width="768" height="748" frameborder="0" scrolling="no"></iframe>
        <?endif;?>
        <div id="music" class="onload">
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
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src=".libs/howler-js/howler.core.min.js"></script>
<script type="text/javascript" src=".libs/howler-js/howler.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Define the key client variables
<? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
var thisScrollbarSettings = {wheelSpeed:0.3};
// Define the music and sound effects details for use in the script
<?

// Define the root paths for the music and sound files
$mmrpg_music_path = 'prototype/sounds/';
$mmrpg_music_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_music_path;
$mmrpg_music_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_music_path;

// Collect the music index from the database and then output to the JS
$this_music_track_index = rpg_music_track::get_index(true, false, 'music_token', 'music_album+/+music_token');
$this_music_track_index = array_map(function($info){ return array(
    'token' => $info['music_token'],
    'album' => $info['music_album'],
    'game' => $info['music_game'],
    'name' => $info['music_name'],
    'link' => $info['music_link'],
    'loop' => $info['music_loop'],
    'order' => $info['music_order']
    ); }, $this_music_track_index);
//error_log('$this_music_track_index ='.print_r($this_music_track_index, true));
echo 'gameSettings.customIndex.musicIndex = '.json_encode($this_music_track_index).';'.PHP_EOL;

// Collect the sound effects index from the file and then output to the JS
$this_sound_effects_path = $mmrpg_music_rootdir.'misc/sound-effects-curated/';
$this_sound_effects_index = array();
$this_sound_effects_index_raw = file_exists($this_sound_effects_path.'audio.json') ? json_decode(file_get_contents($this_sound_effects_path.'audio.json'), true) : array();
if (!empty($this_sound_effects_index_raw['resources'])
    && !empty($this_sound_effects_index_raw['spritemap'])){
    $this_sound_effects_index['src'] = array();
    $this_sound_effects_index['sprite'] = array();
    foreach ($this_sound_effects_index_raw['resources'] AS $key => $resource){
        if (!preg_match('/\.(ogg|mp3)$/i', $resource)){ continue; }
        $source = 'misc/'.$resource.'?'.MMRPG_CONFIG_CACHE_DATE;
        $this_sound_effects_index['src'][] = $source;
    }
    foreach ($this_sound_effects_index_raw['spritemap'] AS $token => $spritemap){
        $sprite = array();
        $sprite['start'] = ceil($spritemap['start'] * 1000);
        $sprite['end'] = ceil($spritemap['end'] * 1000);
        $sprite['duration'] = $sprite['end'] - $sprite['start'];
        $this_sound_effects_index['sprite'][$token] = $sprite;
    }
}
//error_log('$this_sound_effects_index ='.print_r($this_sound_effects_index, true));
echo 'gameSettings.customIndex.soundsIndex = '.json_encode($this_sound_effects_index).';'.PHP_EOL;

// Collect the sound effect aliases index from teh file and then outpyt to the JS
$raw_json = trim(file_get_contents(MMRPG_CONFIG_ROOTDIR.'includes/sounds.json'));
$raw_json = !empty($raw_json) ? preg_replace('!//.*$!m', '', $raw_json) : '';
$raw_json_array = !empty($raw_json) ? json_decode($raw_json, true) : array();
//error_log('$raw_json_array ='.print_r($raw_json_array, true));
$sound_effects_aliases_index = array();
if (!empty($raw_json_array)){
    foreach ($raw_json_array AS $sfx_category => $sfx_category_info){
        if (!empty($sfx_category_info['index'])){
            $sound_effects_aliases_index = array_merge($sound_effects_aliases_index, $sfx_category_info['index']);
        }
    }
}
//error_log('$sound_effects_aliases_index ='.print_r($sound_effects_aliases_index, true));
echo 'gameSettings.customIndex.soundsAliasesIndex = '.json_encode($sound_effects_aliases_index).';'.PHP_EOL;

?>
</script>
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
        var previousWidth = localStorage.getItem('mmrpg-window-width') || 'small';
        var previousHeight = localStorage.getItem('mmrpg-window-height') || 'small';

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