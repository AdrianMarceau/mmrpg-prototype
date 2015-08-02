<?
/*
 * INDEX PAGE : RESOURCES
 */

// Define the SEO variables for this page
$this_seo_title = 'Resources | About | '.$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources. Being a Mega Man fan-game, this project obviously owes most of it\'s thanks to Capcom and of course, Keiji Inafune. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Tools and Resources';
$this_graph_data['description'] = 'The Mega Man RPG Prototype was not created by person, or even by any one piece of software.  Many different tools and resources were necessary for the game to get to where it is today, and you can find a list of the most notable here on this page.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Resources';

// Start generating the page markup
ob_start();
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Resources Overview</h2>
<div class="subbody">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/time-man/sprite_left_80x80.png);"></div></div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> was not created by person, or even by any one piece of software.  Many different tools and resources were necessary for the game to get to where it is today, and you can find a list of the most notable here on this page. If there are any other items that you think I've forgotten and should be added to the list, please <a href="contact/">let me know</a> using the contact page.</p>
</div>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Tools and Resources</h2>
<div class="subbody" style="margin-bottom: 2px; ">
  <p class="text">
    <a href="http://www.sprites-inc.co.uk/files/Classic/" target="_blank"><strong>Sprites Inc.</strong></a> <span class="pipe">|</span> <span>Resource</span> <span class="pipe">|</span> <em>Official Sprites</em><br />
    Many (and maybe all) sprites that appear in this game were found on the number one Mega Man sprite resource - Sprites Inc. - and without the the website I'm sure many Mega Man fan-games would not have been possible.  Even the custom sprites are based on those found on this website, and I cannot thank the creators and contributors enough for their efforts and the fantastic service they provide.  I highly recommend the website for all your Mega Man sprite needs.  :)
  </p>
</div>
<div class="subbody" style="margin-bottom: 2px; ">
  <p class="text">
    <a href="http://megaman.wikia.com/wiki/Robot_Master" target="_blank"><strong>The Mega Man Knowledge Base</strong></a> <span class="pipe">|</span> <span>Resource</span> <span class="pipe">|</span> <em>Official Names, Weaknesses, Quotes, Data, etc&hellip;</em><br />
    The Mega Man Knowledge Base is used as a constant reference for robot weaknesses, official names, artwork, quotes, and so much more.  This is one of the best resources on the internet for official Mega Man data and without it this game would not have been possible.  Thank you, Mega Man community, for this incredibly useful resource.  :D
  </p>
</div>
<div class="subbody">
  <p class="text">
    <a href="http://media.io/" target="_blank"><strong>media.io</strong></a> <span class="pipe">|</span> <span>Resource</span> <span class="pipe">|</span> <em>Media Conversion Tools</em><br />
    Almost MP3 tracks were converted to Firefox-compatible OGG files using this tool, and it has been incredibly helpful in easing the pain of cross-browser support.  Their online audio conversion is very simple to use and is completely free.  I am so happy that this tool exists and recommend it to anyone interested in HTML game development.
  </p>
  <p class="text">
    <a href="http://audio.online-convert.com/" target="_blank"><strong>audio.online-convert.com</strong></a> <span class="pipe">|</span> <span>Resource</span> <span class="pipe">|</span> <em>Media Conversion Tools</em><br />
    Another tool I used for converting MP3 files is the audio section of the online-convert.com website.  Much like media.io, this tool allows one to convert MP3s and other file formats to the Firefox-required OGG as well as a whole plethora of other formats.  I really like the amount of options offered and the amount detail-tweaking allowed on this website.
  </p>
</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>