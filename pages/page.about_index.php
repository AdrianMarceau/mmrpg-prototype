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
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Prototype Overview</h2>
<div class="subbody">

  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/guts-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Guts Man</div></div>
  <p class="text">
    The <strong>Mega Man RPG Prototype</strong> is an ongoing fan-game project with the goal of creating a progress-saving, no-download, no-install, cross-platform, browser-based Mega Man RPG (or what some would call a <a href="http://www.pbbg.org/" target="_blank" rel="related">PBBG</a>) that combines the addictive collection and battle mechanics of the Pokémon series with the beloved robots and special weapons of the classic Mega Man series. Fight your way through more than fifty different robot masters in a turn-based battle system reminiscent of both play-by-post forum games and early 8-bit role-playing games.
  </p>

  <div class="float float_left"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/air-man/sprite_right_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Air Man</div></div>
  <p class="text">This game is written with a combination of HTML, CSS, Javascript, <a href="http://jquery.com/" target="_blank">jQuery</a>, and PHP, and is completely DOM-based on the front-end (no &lt;canvas&gt; here, folks!).  The game uses a great deal of assets from official <a href="http://www.capcom.com/" target="_blank">Capcom</a> games and is derivative in every sense of the word, but still has a lot of new ideas and content to offer including a considerable amount of custom sprite work for robots, abilities, players, and fields as well as clever mechanics, addictive gameplay, and fun new twists on the classic Mega Man formula.</p>

</div>

<div class="subbody" style="margin-top: 20px;">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/robot/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Robot</div></div>
  <p class="text">
  (!) If you would like to contribute to this game with story ideas, <del>sprites</del>, stat balancing, bug testing, or anything else please <a href="contact/">contact me</a> or post in <a href="community/">our community</a> and we can discuss the details. The robots, mechas, and fields for MM1, MM2, and MM4 are complete, robots from MM3, MM4, and several from other games are complete (but not their stages) and new stuff is being worked on all the time so our exact requirements are always changing.  Whatever you end up working on, your work will be credited appropriately and all help is appreciated. :)
  </p>
</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>