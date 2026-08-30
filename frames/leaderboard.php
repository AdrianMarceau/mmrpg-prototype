<?
// Require the application top file
require_once('../top.php');

// Require the leaderboard data file
$this_current_page = 'prototype';
require_once(MMRPG_CONFIG_ROOTDIR.'includes/leaderboard.php');

// Collect the session token
$session_token = mmrpg_game_token();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Leaderboard | Prototype | Mega Man RPG Prototype</title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/events.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/leaderboard.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<? require(MMRPG_CONFIG_ROOTDIR.'scripts/gamescripts.prototype.php'); ?>
<? require(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.all.php'); ?>
<script type="text/javascript">
gameSettings.fadeIn = <?= $this_start_key == 0 ? 'true' : 'false' ?>;
// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
$(document).ready(function(){
    // Start playing the data base music
    //top.mmrpg_music_load('misc/data-base');

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisWindow = $(window);

    thisWindow.resize(function(){ windowResizeLeaderboard(); });
    setTimeout(function(){ windowResizeLeaderboard(); }, 1000);
    windowResizeLeaderboard();

    var windowHeight = $(window).height();
    var htmlHeight = $('html').height();
    var htmlScroll = $('html').scrollTop();
    //alert('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

    // Hijack any href links for ipad fixing
    $('a[href]', thisBody).click(function(e){
    e.preventDefault();
    window.location.href = $(this).attr('href');
    });

    // Wait for this page's images to finish loading
    thisBody.waitForImages(function(){
        var tempTimeout = setTimeout(function(){
            <? if($this_start_key == 0): ?>
            // Fade in the leaderboard screen slowly
            thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing');
            <? else: ?>
            // Unhide the leadboard screen quickly
            thisBody.css({opacity:1}).removeClass('hidden');
            <? endif; ?>
            // Let the parent window know the menu has loaded
            parent.prototype_menu_loaded();
            }, 1000);
        }, false, true);


});
// Create the windowResize event for this page
function windowResizeLeaderboard(){

    var windowWidth = thisWindow.width();
    var windowHeight = thisWindow.height();
    var headerHeight = $('.header', thisBody).outerHeight(true);

    var newBodyHeight = windowHeight;
    var newFrameHeight = newBodyHeight - headerHeight;

    if (windowWidth > 800){ thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
    else { thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }

    thisBody.css({height:newBodyHeight+'px'});
    thisPrototype.css({height:newBodyHeight+'px'});
    $('.leaderboard, .leaderboard .wrapper', thisPrototype).css({height:newFrameHeight+'px'});

    //alert('windowWidth = '+windowWidth+'; windowHeight = '+windowHeight+'; bannerHeight = '+bannerHeight+'; ');

}
</script>
</head>
<body id="mmrpg" class="iframe" data-frame="leaderboard">

    <div id="prototype" class="<?= $this_start_key == 0 ? 'hidden' : '' ?>">

        <div class="menu">

            <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                <span class="count">
                    <i class="fa fas fa-trophy"></i>
                    Battle Points Leaderboard
                    <span class="progress">(
                        <?= !empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : 0 ?>
                        <?= $this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '' ?>
                        )</span>
                </span>
            </span>

            <div class="leaderboard">
                <div class="wrapper" style="<?= $flag_wap ? 'margin-right: 0;' : '' ?>">
                <?

                // Print out the generated leaderboard markup
                //echo $this_leaderboard_markup;
                //error_log('$this_leaderboard_markup('.count($this_leaderboard_markup).') = '.print_r($this_leaderboard_markup, true));
                //error_log('$this_leaderboard_index('.count($this_leaderboard_index).') = [...]');
                if (!empty($this_leaderboard_markup)){
                    $last_key = 0;
                    foreach ($this_leaderboard_markup AS $key => $leaderboard_markup){
                        // If this key is below the start limit, don't display
                        if (empty($leaderboard_markup)){ continue; }
                        // Update the last key variable
                        $last_key = $key;
                        // Display this save file's markup
                        $leaderboard_markup = preg_replace('/href="([^<>]+)"/', '', $leaderboard_markup);
                        echo $leaderboard_markup;
                    }

                    // Define the start key for the next batch of players
                    $start_key = $last_key + 1;

                    // Calculate pagination variables
                    $total_pages = ceil($this_leaderboard_count / $this_display_limit_default);
                    $current_page = floor($this_start_key / $this_display_limit_default) + 1;
                    $window = 2; // Adjust this to show more/fewer pages around the current page

                    // Print out the opening tag for the container div
                    echo '<div class="container" style="text-align: center; margin: 10px 0;">';

                    // Previous Page Link
                    if ($current_page > 1){
                        $prev_start = ($current_page - 2) * $this_display_limit_default;
                        $prev_limit = ($current_page - 1) * $this_display_limit_default;
                        echo '<a class="more prev" href="frames/leaderboard.php?start='.$prev_start.'&amp;limit='.$prev_limit.'">&laquo; Prev</a> ';
                    }

                    // Numbered Page Links
                    for ($i = 1; $i <= $total_pages; $i++){
                        // Show first, last, and window around the current page
                        if ($i == 1 || $i == $total_pages
                            || ($i >= $current_page - $window && $i <= $current_page + $window)){
                            $page_start = ($i - 1) * $this_display_limit_default;
                            $page_limit = $i * $this_display_limit_default;
                            if ($i == $current_page){
                                // Current page styling
                                echo '<span class="more current" style="opacity: 0.5; cursor: default;">'.$i.'</span> ';
                            } else {
                                // Link to other pages
                                echo '<a class="more page" href="frames/leaderboard.php?start='.$page_start.'&amp;limit='.$page_limit.'">'.$i.'</a> ';
                            }
                        } elseif ($i == $current_page - $window - 1
                            || $i == $current_page + $window + 1){
                            // Ellipsis for skipped pages
                            echo '<span class="more ellipsis" style="opacity: 0.5; cursor: default; border: none; background: transparent;">...</span> ';
                        }
                    }

                    // Next Page Link
                    if ($current_page < $total_pages){
                        $next_start = $current_page * $this_display_limit_default;
                        $next_limit = ($current_page + 1) * $this_display_limit_default;
                        echo '<a class="more next" href="frames/leaderboard.php?start='.$next_start.'&amp;limit='.$next_limit.'">Next &raquo;</a>';
                    }

                    // Print out the scroll padding
                    echo '<div class="file" style="visibility: hidden; height: 100px;">&nbsp;</div>';

                    // Print out the closing container div
                    echo '</div>';


                }

                ?>

                </div>
            </div>

        </div>

    </div>
<script type="text/javascript">
$(document).ready(function(){

});
</script>
<?

// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php'); }

// Unset the database variable
unset($db);

?>
</body>
</html>