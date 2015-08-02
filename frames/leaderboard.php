<?
// Require the application top file
require_once('../top.php');

// Require the leaderboard data file
require_once('../data/leaderboard.php');

// Collect the session token
$session_token = mmrpg_game_token();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title><?= !MMRPG_CONFIG_IS_LIVE ? '@ ' : '' ?>View Leaderboard | Mega Man RPG Prototype Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
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

      <span class="header block_1">Battle Points Leaderboard <span style="opacity: 0.25;">( <?= !empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : 0 ?><?= $this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '' ?> )</span></span>

      <div class="leaderboard">
        <div class="wrapper" style="<?= $flag_wap ? 'margin-right: 0;' : '' ?>">
        <?

        // Print out the generated leaderboard markup
        if (!empty($this_leaderboard_markup)){
          $last_key = 0;
          foreach ($this_leaderboard_markup AS $key => $leaderboard_markup){
            // If this key is below the start limit, don't display
            if ($key < $this_start_key){ continue; }
            // Update the last key variable
            $last_key = $key;
            // Display this save file's markup
            //$leaderboard_markup = preg_replace('/<span class="username">([^<>]+)?<\/span>/', '<h2 class="username">$1</h2>', $leaderboard_markup);
            $leaderboard_markup = preg_replace('/href="([^<>]+)"/', '', $leaderboard_markup);
            echo $leaderboard_markup;
            // Only show listings up to the display limit
            if ($key + 1 >= $this_display_limit){ break; }
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
<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'unlocked-tooltip_battle-leaderboard-intro';
if (empty($_SESSION[$session_token]['DEMO']) && empty($temp_game_flags[$temp_event_flag])){
  $temp_game_flags[$temp_event_flag] = true;
  ?>
  // Generate a first-time event canvas that explains how the editor works
  gameSettings.windowEventsCanvas = [
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/field/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/proto-man/sprite_right_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 20px; left: 120px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 35px; left: 240px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/bass/sprite_right_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 20px; right: 120px; width: 80px; height: 80px;">&nbsp;</div>'+
    ''
    ];
  // Generate a first-time event message that explains how the editor works
  gameSettings.windowEventsMessages = [
    '<p>The <strong>Mega Man RPG Prototype</strong> currently has <?= $this_leaderboard_count ?> players and that number is growing all the time. Throughout the course of the game, players collect Battle Points from missions and those points build up to unlock new abilities and other new content.</p>'+
    '<p>Not all players are created equal, however, and some clearly stand above the rest in terms of their commitment to the game and their skill at exploiting the battle system\'s mechanics. In the spirit of competition, all players have been ranked by their total Battle Point scores and listed from highest to lowest.</p>'+
    '<p>Use the numbered links at the bottom of the page to navigate through the listings.</p>'+
    ''
    ];
  // Push this event to the parent window and display to the user
  top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
  <?
}
?>
});
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
// Unset the database variable
unset($DB);
?>
</body>
</html>