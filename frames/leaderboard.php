<?
// Require the application top file
require_once('../top.php');

// Require the leaderboard data file
require_once(MMRPG_CONFIG_ROOTDIR.'includes/leaderboard.php');

// Collect the session token
$session_token = mmrpg_game_token();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Leaderboard | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/leaderboard.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= $this_start_key == 0 ? 'true' : 'false' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
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
<body id="mmrpg" class="iframe">

    <div id="prototype" class="<?= $this_start_key == 0 ? 'hidden' : '' ?>">

        <div class="menu">

            <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                <span class="count">
                    Battle Points Leaderboard
                    <span style="opacity: 0.25;">( <?= !empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : 0 ?><?= $this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '' ?> )</span>
                </span>
            </span>

            <div class="leaderboard">
                <div class="wrapper" style="<?= $flag_wap ? 'margin-right: 0;' : '' ?>">
                <?

                // Print out the generated leaderboard markup
                //echo $this_leaderboard_markup;
                //die('<pre>'.print_r($this_leaderboard_markup, true).'</pre>');
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

                    // Print out the opening tag for the container dig
                    echo '<div class="container">';

                    // If not displaying all players, create a link to show more
                    if ($this_display_limit > $this_display_limit_default){
                        $new_display_limit = $this_display_limit - $this_display_limit_default;
                        $new_start_key = $start_key - $this_display_limit_default - $this_display_limit_default;
                        if ($new_display_limit < $this_display_limit_default){ $new_display_limit = 0; }
                        if ($new_start_key < 0){ $new_start_key = 0; }
                        echo '<a class="more" name="more_link" href="frames/leaderboard.php?'.(!empty($new_start_key) ? 'start='.$new_start_key.'&amp;' : '').(!empty($new_start_key) ? 'limit='.$new_display_limit : '').'" >&laquo; Previous Page</a>';
                    }
                    if ($this_display_limit < $this_leaderboard_count){
                        $new_display_limit = $this_display_limit + $this_display_limit_default;
                        if ($new_display_limit > $this_leaderboard_count){ $new_display_limit = $this_leaderboard_count; }
                        echo '<a class="more" name="more_link" href="frames/leaderboard.php?start='.$start_key.'&amp;limit='.$new_display_limit.'" >Next Page &raquo;</a>';
                    }
                    if ($this_display_limit >= $this_leaderboard_count){
                        echo '<a class="more" name="more_link" href="frames/leaderboard.php?start=0&amp;limit='.$this_display_limit_default.'">&laquo; First Page</a>';
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