<?
// Require the application top file
require_once('../top.php');

// Require the tooltip data file
//require_once('../data/prototype.php');

// Define the available tooltips based on what has been completed
$prototype_tooltip_index = array();
$prototype_tooltip_index[] = 'Robots fight against each other from opposite sides of the field in one-on-one battles, but each player can have up to seven extra robots on their bench for backup.';
$prototype_tooltip_index[] = 'When a robot\'s Life Energy reaches zero, it becomes disabled and unusable in battle. The first player to disable all robots on the opposite side of the field wins the fight.';
$prototype_tooltip_index[] = 'Weapon Energy is depleted when using abilities but automatically recovers by one unit each turn. Switch out to another robot if you need to recharge, as benched robots recover twice as fast.';
$prototype_tooltip_index[] = 'The base damage dealt by an ability is relative to the user\'s Attack stat versus the target\'s Defense stat. If Attack is higher the ability will do more damage and if Defense is higher it will do less.';
$prototype_tooltip_index[] = 'Using the scan option in battle does not take up a turn and provides valuable information about the target\'s core type, current stats, weaknesses, resistances, affinities, and immunities.';
$prototype_tooltip_index[] = 'Using the switch option in battle does not take up a turn and allows you to rearrange your robots offensively or defensively at any time.';
$prototype_tooltip_index[] = 'Experience points are divided among all active robots when a target is disabled. Taking fewer into battle is risky but will yield greater rewards.';
$prototype_tooltip_index[] = 'Field multipliers appear at the bottom of the battle window and temporarily alter the damage and/or recovery power of all abilities.';
$prototype_tooltip_index[] = 'If a robot uses an ability that matches its Core Type, that ability will inflict 50% more damage and 50% more recovery in battle.';
$prototype_tooltip_index[] = 'Use super effective attacks and land critical hits for more experience points in battle.  Use the scan option for detailed info about your target.';
$prototype_tooltip_index[] = 'If a robot has a weakness to either of an ability\'s types, that robot will receive twice as much damage and recovery from that ability in battle.';
$prototype_tooltip_index[] = 'If a robot has a resistance to either of an ability\'s types, that robot will receive half as much damage and recovery from that ability in battle.';
$prototype_tooltip_index[] = 'If a robot has an affinity to either of an ability\'s types, that robot will receive inverted damage and recovery from that ability in battle.';
$prototype_tooltip_index[] = 'If a robot has an immunity to either of an ability\'s types, that robot will receive no damage or recovery from that ability in battle.';
$prototype_tooltip_index[] = 'If a robot has a weakness to either of a recovery ability\'s types, that robot will receive damage from that ability in battle instead.';
$prototype_tooltip_index[] = 'If a robot has an affinity to either of a damaging ability\'s types, that robot will receive recovery from that ability in battle instead.';
$prototype_tooltip_index[] = 'The Buster Shot is unique in that it requires zero Weapon Energy to use in battle.  Very useful when you\'re out of ammo and need to recharge!';
$prototype_tooltip_index[] = 'Elemental abilities are powerful but can damage the target robot\'s data.  Defeat key robots using only neutral abilities to unlock them for battle!';
//$prototype_tooltip_index[] = '';
//$prototype_tooltip_index[] = '';
//$prototype_tooltip_index[] = '';
//$prototype_tooltip_index[] = '';
//$prototype_tooltip_index[] = '';
//$prototype_tooltip_index[] = '';

if (!empty($_SESSION[mmrpg_game_token()]['DEMO'])){

  // Define the demo-mode specific player tooltips
  $prototype_tooltip_index[] = 'Progress cannot be saved in the demo mode, but creating a new save file will let you start your adventure with any points earned thus far.';

} else {

  // Define the normal mode tooltips
  $unlock_count_players = mmrpg_prototype_players_unlocked();
  $prototype_tooltip_index[] = 'Benched robots take reduced damage based on their distance from the attacker. Use this to your advantage and put your weakest in the back!';
  $prototype_tooltip_index[] = 'Equip as many abilities as you can - there is no penalty for having multiple weapons and it\'s always better to be prepared for battle.';
  $prototype_tooltip_index[] = 'The enemy robots that appear in battle grow in level as you progress through the game, so replay old missions if you\'re having a hard time.';
  $prototype_tooltip_index[] = 'Most missions can be replayed any number of times and the robots that appear in them will grow by one level with each victory.';
  $prototype_tooltip_index[] = 'Revived robot masters can be unlocked for use in battle if defeated using only Neutral type abilities like the Buster Shot or Mega Buster.';
  if ($unlock_count_players >= 2){
    $prototype_tooltip_index[] = 'Robots can be tranferred between players by clicking the player name in the robot editor and selecting the new owner from the dropdown.';
    $prototype_tooltip_index[] = 'Transferred robots receive twice the experience points in battle, but do not benefit from player-based stat bonuses to attack, defense, or speed.';
  }

}

// Count the number of battle tips generated
$this_tooltip_count = !empty($prototype_tooltip_index) ? count($prototype_tooltip_index) : 0;



?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title><?= !MMRPG_CONFIG_IS_LIVE ? '@ ' : '' ?>View Help | Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/help.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= empty($this_start_key) ? 'true' : 'false' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?= MMRPG_CONFIG_CACHE_DATE ?>';
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
      <? if(empty($this_start_key)): ?>
      // Fade in the tooltip screen slowly
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
  $('.menu', thisPrototype).css({height:newBodyHeight+'px'});
  $('.menu .help', thisPrototype).css({height:(newBodyHeight - headerHeight)+'px'});
  $('.menu .help .wrapper', thisPrototype).css({height:(newBodyHeight - headerHeight)+'px'});
  $('.tooltip, .tooltip .wrapper', thisPrototype).css({height:newFrameHeight+'px'});

  //console.log('windowWidth = '+windowWidth+'; windowHeight = '+windowHeight+'; newFrameHeight = '+newFrameHeight+'; ');

}
</script>
</head>
<body id="mmrpg" class="iframe">

  <div id="prototype">

    <div class="menu">

      <span class="header block_1">Battle Tips <span style="opacity: 0.25;">( <?= !empty($this_tooltip_count) ? ($this_tooltip_count == 1 ? '1 Tip' : $this_tooltip_count.' Tips') : 0 ?> )</span></span>

      <div class="help">
        <div class="wrapper" style="<?= $flag_wap ? 'margin-right: 0;' : '' ?>">
        <?

        // Print out the generated tooltip markup
        //echo $this_tooltip_markup;
        //die('<pre>'.print_r($this_tooltip_markup, true).'</pre>');
        if (!empty($prototype_tooltip_index)){
          $last_key = 0;
          foreach ($prototype_tooltip_index AS $key => $tooltip_markup){
            // Update the last key variable
            $last_key = $key;
            // Display this battle tips markup
            echo '<div class="rule"><strong class="number"><span class="sup">TIP</span>#'.($key + 1).'</strong><p class="text">'.$tooltip_markup.'</p></div>';
          }

          // Define the start key for the next batch of players
          $start_key = $last_key + 1;

          // Print out the opening tag for the container dig
          echo '<div class="container">';

          // Print out the scroll padding
          echo '<div class="file" style="visibility: hidden; height: 50px;">&nbsp;</div>';

          // Print out the closing container div
          echo '</div>';


        }

        ?>

        </div>
      </div>

    </div>

  </div>

<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
// Unset the database variable
unset($DB);
?>
</body>
</html>