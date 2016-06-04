<?
/*
 * INDEX PAGE : ABOUT
 */

// Define the SEO variables for this page
$this_seo_title = 'About | '.$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype is an ongoing fan-game project with the goal of creating a no-install, cross-platform, browser-based, progress-saving, Mega Man RPG that combines the addictive collection and battle mechanics of the Pokémon series with the beloved robots and special weapons of the classic Mega Man series. Battle through more than thirty robot masters in classic RPG style with either Dr. Light and Mega Man or Dr. Wily and Proto Man in the wonderful and strange little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'About the Prototype';
$this_graph_data['description'] = 'The Mega Man RPG Prototype is an ongoing fan-game project with the goal of creating a no-install, cross-platform, browser-based, progress-saving, Mega Man RPG that combines the addictive collection and battle mechanics of the Pokémon series with the beloved robots and special weapons of the classic Mega Man series. Battle through more than thirty robot masters in classic RPG style with either Dr. Light and Mega Man or Dr. Wily and Proto Man in the wonderful and strange little time waster.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';


// Define the MARKUP variables for this page
$this_markup_header = 'About the Mega Man RPG Prototype';

// Start generating the page markup
ob_start();
?>

<a name="overview" class="anchor">&nbsp;</a>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Mega Man RPG Prototype Overview</h2>
<div class="subbody">

  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/guts-man/sprite_left_80x80.png);">Guts Man</div></div>
  <p class="text">
    The <strong>Mega Man RPG Prototype</strong> is an ongoing fan-game project with the goal of creating a progress-saving, no-download, no-install, cross-platform, browser-based Mega Man RPG (or what some would call a <a href="http://www.pbbg.org/" target="_blank" rel="related">PBBG</a>) that combines the addictive collection and battle mechanics of the Pokémon series with the beloved robots and special weapons of the classic Mega Man series. Fight your way through more than fifty different robot masters in a turn-based battle system reminiscent of both play-by-post forum games and early 8-bit role-playing games.
  </p>

  <div class="float float_left"><div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/air-man/sprite_right_80x80.png);">Air Man</div></div>
  <p class="text">This game is written with a combination of HTML, CSS, Javascript, <a href="http://jquery.com/" target="_blank">jQuery</a>, and PHP, and is completely DOM-based on the front-end (no &lt;canvas&gt; here, folks!).  The game uses a great deal of assets from official <a href="http://www.capcom.com/" target="_blank">Capcom</a> games and is derivative in every sense of the word, but still has a lot of new ideas and content to offer including a considerable amount of custom sprite work for robots, abilities, players, and fields as well as clever mechanics, addictive gameplay, and fun new twists on the classic Mega Man formula.</p>

</div>


<?
/*
// Require the screenshots data for display
require_once('data/screenshots.php');
?>
<a name="screenshots" class="anchor">&nbsp;</a>
<h2 class="subheader"><a class="link" href="http://imgur.com/a/MJCVi" target="_blank" rel="screenshots">Mega Man RPG Prototype Screenshots</a><a class="float_link" href="<?= $mmrpg_screenshots_album ?>" target="_blank">View More Screenshots &raquo;</a></h2>
<?
// Loop through the screenshot array and display its contents
$temp_screenshot_counter = 0;
$temp_screenshot_limit = 5;
echo '<div class="subwrap">';
foreach ($mmrpg_screenshots_array AS $group_yyyymmdd => $group_screenshots){
  $this_group_date = preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#', '$1/$2/$3', $group_yyyymmdd);
  //echo '<h2 class="subheader">'.date('F jS, Y', strtotime($this_group_date)).'</h2>';
  //echo '<div class="subwrap">';
  foreach ($group_screenshots AS $screenshot_key => $screenshot_info){
    $this_screenshot_title = $screenshot_info['image_title'];
    $this_screenshot_link = 'http://imgur.com/'.$screenshot_info['image_token'].'';
    $this_screenshot_thumb = 'http://i.imgur.com/'.$screenshot_info['image_token'].'l.png';
    $this_screenshot_alt = $this_screenshot_title.' | Mega Man RPG Prototype Screenshot | '.$this_group_date;
    echo '<a class="image image_1x5" href="'.$this_screenshot_link.'" target="_blank"><img src="'.$this_screenshot_thumb.'" alt="'.$this_screenshot_alt.'" title="'.$this_screenshot_title.'" width="100%" /><label>'.$this_screenshot_title.'</label></a>';
    $temp_screenshot_counter++;
    if ($temp_screenshot_counter >= $temp_screenshot_limit){ break;  }
  }
  //echo '</div>';
  if ($temp_screenshot_counter >= $temp_screenshot_limit){ break;  }
}
echo '</div>';
*/
?>

<a name="development" class="anchor">&nbsp;</a>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Mega Man RPG Prototype Mechanics &amp; Development</h2>
<div class="subbody">

  <div class="float float_left"><div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/robots/crash-man/sprite_right_80x80.png);">Crash Man</div></div>
  <p class="text">The rock-paper-scissors weakness cycle in the classic Mega Man series has striking similarities to the weakness/resistance cycle found in some RPGs and - specifically - Pokémon.  The <strong>Mega Man RPG Prototype</strong> exploits this similarity in fun and interesting ways, allowing the player to take control of multiple robots outside of using the usual blue hero and battle through a gauntlet of Robot Master opponents, each with their own set of weaknesses, resistances, affinities, immunities, and abilities!</p>

  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/robots/bomb-man/sprite_left_80x80.png);">Bomb Man</div></div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> allows visitors to play the game as often or as infrequently as they desire, with the ability to create a personal save file and come back at a later date to resume their game.  Save files can be loaded from any machine that has a capable, modern browser, and allows sharing of game progress across multiple desktop and mobile devices. Each robot has their own unique set of stats, strengths, weaknesses, and unlockable abilities, and with each robot's abilities being completely customizable the combinations are endless.</p>

  <div class="float float_left"><div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/flash-man/sprite_right_80x80.png);">Flash Man</div></div>
  <p class="text">Though the game is fully playable in its current state, it is always in development and as such things are constantly changing.  I apologize for bugs or confusion you may experience during this process, but we hope that you enjoy what we&#39;ve created so far.   Also, it never hurts to send us your feedback!  Thank you so much for visiting this website and for playing our game - we look forward to seeing everyone on the <a href="leaderboard/">Leaderboard</a>!</p>

</div>





<a name="links" class="anchor">&nbsp;</a>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Mega Man RPG Prototype Links</h2>
<div class="subbody">

  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_05" style="background-image: url(images/robots/cut-man/sprite_left_80x80.png);">Cut Man</div></div>
  <div class="text">
    <p>
      <a href="https://www.facebook.com/megamanrpgprototype" target="_blank"><strong>Official Facebook Page</strong></a><br />
      The official Facebook page for the prototype is primarily a mirror for the updates posted in the community, but special password and teaser screenshots are occasionally posted as well.<br />
    </p>
    <p>
      <a href="http://megaman.wikia.com/wiki/Mega_Man_RPG_Prototype" target="_blank"><strong>Official Wiki Page</strong></a><br />
      The official Wiki page for the prototype on the Mega Man Knowledge Base, created by MegabossMan and maintained by myself and the community.<br />
    </p>
    <p>
      <a href="http://www.youtube.com/playlist?list=PL2yhjPks7HSo_vJNq02ls_DwhmrA2pmU5" target="_blank"><strong>YouTube Walkthrough Videos</strong></a><br />
      A playlist of official walkthrough videos by myself, created primarily to show new and potential players the flow of battle and one possible order for defeating the bosses.
    </p>
    <p>
      <a href="http://www.youtube.com/playlist?list=PL2yhjPks7HSqcclaAJTD0FAVYvr7qbm_i" target="_blank"><strong>YouTube Let's Play Videos</strong></a><br />
      Any and all YouTube videos related to the game are collected in this super-playlist by myself, including Let&#39;s Plays, demo shots, and teasers.<br />
      Included videos from : <a href="http://www.youtube.com/user/Ageman20XX" target="_blank">Ageman20XX</a>, <a href="http://www.youtube.com/channel/UCV3zL7VVn9BRsW3wAcLCwRQ" target="_blank">BladeWolfe</a>, <a href="http://www.youtube.com/user/EphNE" target="_blank">EphNE</a>, <a href="http://www.youtube.com/user/LizandRobbie" target="_blank">LizandRob</a>, <a href="http://www.youtube.com/user/BroganGames1" target="_blank">BroganGames</a>, <a href="http://www.youtube.com/user/DerivedGod" target="_blank">DerivedGod</a><br />
    </p>
    <p>
      <a href="http://www.reddit.com/r/gaming/comments/15401g/mega_man_rpg_prototype_a_crossplatform_browser/" target="_blank"><strong>Reddit Discussion Thread</strong></a><br />
      A large discussion thread on Reddit with bug reports, answers to various questions, and development talk.<br />
      Follow up posts : <a href="http://www.reddit.com/tb/1awijl" target="_blank">1</a>, <a href="http://www.reddit.com/tb/1awiy9" target="_blank">2</a>, <a href="http://www.reddit.com/r/letsplay/comments/1ov2l6/looking_for_betatesters_who_can_make_lets_play/" target="_blank">3</a>, <a href="http://www.reddit.com/r/playmygame/comments/1pbehp/completed_mega_man_rpg_prototype_fullyrealized/" target="_blank">4</a>, <a href="http://www.reddit.com/tb/1pbfqt" target="_blank">5</a>.<br />
    </p>
    <p>
      <a href="http://plutolighthouse.net/blog/tag/megaman-rpg-prototype/" target="_blank"><strong>PlutoLighthouse.NET Blog</strong></a><br />
       Prior to building the <a href="community/news/">News &amp;s Updates</a> section of the community, all development-related talk was posted to my blog on PlutoLighthouse.NET.  Outdated but interesting information if you like dev-talk.<br />
    </p>
    <p>
      <a href="http://pbbg.wikidot.com/browsermmo:mega-man-rpg-prototype" target="_blank"><strong>Crane &amp; Dragon PBBG List</strong></a><br />
      Crane &amp; Dragon is a large directory of the best free Persistent Browser Based Games (PBBG&#39;s) on the web.
    </p>
    <p>
      <a href="http://www.themmnetwork.com/2013/01/10/fan-made-mega-man-rpg-get-your-armies-ready/" target="_blank"><strong>The MegaMan Network Thread</strong></a><br />
      More discussion and development talk regarding the game.  Not too much activity here, unfortunately.<br />
    </p>
    <p>
      <a href="http://imgur.com/a/MJCVi" target="_blank"><strong>Imgur Screenshots Album</strong></a><br />
      Redundant since the local <a href="gallery/">Gallery</a> page was created, but still updated randomly.<br />
    </p>
  </div>
</div>




<div class="subbody" style="margin-top: 20px;">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/robot/sprite_left_80x80.png);">Robot</div></div>
  <p class="text">
  (!) If you would like to contribute to this game with story ideas, <del>sprites</del>, stat balancing, bug testing, or anything else please <a href="contact/">contact me</a> or post in <a href="community/">our community</a> and we can discuss the details. The robots, mechas, and fields for MM1, MM2, and MM4 are complete, robots from MM3, MM4, and several from other games are complete (but not their stages) and new stuff is being worked on all the time so our exact requirements are always changing.  Whatever you end up working on, your work will be credited appropriately and all help is appreciated. :)
  </p>
</div>

<div class="subbody">
  <a href="http://www.browser-games-hub.org/game/98/Mega+Man+RPG+Prototype/" target="_blank" rel="related">
	<img src="http://www.browser-games-hub.org/images/browsergameshub.png"
		alt="Browser Games Hub logo"
		title="Mega Man RPG Prototype supports the Browser Games Hub API" />
  </a>
</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>