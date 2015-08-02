<?
/*
 * INDEX PAGE : RESOURCES
 */

// Define the SEO variables for this page
$this_seo_title = 'Links | About | '.$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources. Being a Mega Man fan-game, this project obviously owes most of it\'s thanks to Capcom and of course, Keiji Inafune. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'External Links';
$this_graph_data['description'] = 'The Mega Man RPG Prototype was not created by person, or even by any one piece of software.  Many different tools and resources were necessary for the game to get to where it is today, and you can find a list of the most notable here on this page.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Links';

// Start generating the page markup
ob_start();
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Links Overview</h2>
<div class="subbody">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/cut-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Cut Man</div></div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> was not created by person, or even by any one piece of software.  Many different tools and resources were necessary for the game to get to where it is today, and you can find a list of the most notable here on this page. If there are any other items that you think I've forgotten and should be added to the list, please <a href="contact/">let me know</a> using the contact page.</p>
</div>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">External Links</h2>
<div class="subbody">
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
      Follow up posts : <a href="http://www.reddit.com/tb/1awijl" target="_blank">1</a>, <a href="http://www.reddit.com/tb/1awiy9" target="_blank">2</a>, <a href="http://www.reddit.com/r/letsplay/comments/1ov2l6/looking_for_betatesters_who_can_make_lets_play/" target="_blank">3</a>, <a href="http://www.reddit.com/r/playmygame/comments/1pbehp/completed_mega_man_rpg_prototype_fullyrealized/" target="_blank">4</a>, <a href="http://www.reddit.com/tb/1pbfqt" target="_blank">5</a>, <a href="http://www.reddit.com/r/playmygame/comments/2ha3mv/completedfreedemo_mmrpg_prototype_a_humble_little/" target="_blank">6</a><br />
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

<div class="subbody" style="margin-top: -5px;">
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