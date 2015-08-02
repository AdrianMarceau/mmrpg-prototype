<?
/*
 * INDEX PAGE : RESOURCES
 */

// Define the SEO variables for this page
$this_seo_title = 'Mechanics | About | '.$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources. Being a Mega Man fan-game, this project obviously owes most of it\'s thanks to Capcom and of course, Keiji Inafune. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Mechanics and Development';
$this_graph_data['description'] = 'The Mega Man RPG Prototype was not created by person, or even by any one piece of software.  Many different tools and resources were necessary for the game to get to where it is today, and you can find a list of the most notable here on this page.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Mechanics';

// Start generating the page markup
ob_start();
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Mechanics Overview</h2>
<div class="subbody">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/wood-man/sprite_left_80x80.png);"></div></div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> was not created by person, or even by any one piece of software.  Many different tools and resources were necessary for the game to get to where it is today, and you can find a list of the most notable here on this page. If there are any other items that you think I've forgotten and should be added to the list, please <a href="contact/">let me know</a> using the contact page.</p>
</div>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Mechanics and Development</h2>
<div class="subbody">

  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/crash-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Crash Man</div></div>
  <p class="text">The rock-paper-scissors weakness cycle in the classic Mega Man series has striking similarities to the weakness/resistance cycle found in some RPGs and - specifically - Pokémon.  The <strong>Mega Man RPG Prototype</strong> exploits this similarity in fun and interesting ways, allowing the player to take control of multiple robots outside of using the usual blue hero and battle through a gauntlet of Robot Master opponents, each with their own set of weaknesses, resistances, affinities, immunities, and abilities!</p>

  <div class="float float_left"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/bomb-man/sprite_right_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Bomb Man</div></div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> allows visitors to play the game as often or as casually as they desire, with the ability to create a personal save file and come back at a later date to resume their game.  Save files can be loaded from any machine that has a capable, modern browser, and allows sharing of game progress across multiple desktop and mobile devices. Each robot has their own unique set of stats, strengths, weaknesses, and unlockable abilities, and with each robot's abilities being completely customizable the combinations are endless.</p>

  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/flash-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Flash Man</div></div>
  <p class="text">Though the game is fully playable in its current state, it is always in development and as such things are constantly changing.  I apologize for bugs or confusion you may experience during this process, but we hope that you enjoy what we&#39;ve created so far.   Also, it never hurts to send us your feedback!  Thank you so much for visiting this website and for playing our game - we look forward to seeing everyone on the <a href="leaderboard/">Leaderboard</a>!</p>

</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>