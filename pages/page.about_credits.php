<?
/*
 * INDEX PAGE : CREDITS
 */

// Define the SEO variables for this page
$this_seo_title = 'Credits | About | '.$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources. Being a Mega Man fan-game, this project obviously owes most of it\'s thanks to Capcom and of course, Keiji Inafune. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Credits and Contributors';
$this_graph_data['description'] = 'The Mega Man RPG Prototype was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources. Being a Mega Man fan-game, this project obviously owes most of it\'s thanks to Capcom and of course, Keiji Inafune.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Credits';

// Collect user data for all contributors in the database
$contributor_ids = array(
  412,  // AdrianMarceau (Developer)
  92,  // ChillPenguin (Administrator)
  3842,  // MegaBossMan (Administrator)
  2,  // Brorman (Contributor)
  110,  // EliteP1 / MMX100 (Contributor)
  18,  // MetalMan (Contributor)
  435,  // Spinstrike (Contributor)
  4117,  // Rhythm_BCA (Contributor)
  4091,  // CHAOSFANTAZY (Moderator)
  //4831,  // ThatGuyNamedMikey (Moderator)
  4307,  // Reisrat (Moderator)
  1330, // TheDoc (Moderator)
  );
  // 484 Ephnee
$contributor_index = $DB->get_array_list("SELECT * FROM mmrpg_users LEFT JOIN mmrpg_roles ON mmrpg_users.role_id = mmrpg_roles.role_id WHERE user_id IN (".implode(', ', $contributor_ids).")", 'user_id');
//die(print_r($contributor_index, true));
function temp_sort_by_date($u1, $u2){
  global $contributor_ids;
  if ($u1['user_id'] == 412){ return -1; }
  elseif ($u2['user_id'] == 412){ return 1; }
  elseif (array_search($u1['user_id'], $contributor_ids) < array_search($u2['user_id'], $contributor_ids)){ return -1; }
  elseif (array_search($u1['user_id'], $contributor_ids) > array_search($u2['user_id'], $contributor_ids)){ return 1; }
  elseif ($u1['user_date_created'] < $u2['user_date_created']){ return -1; }
  elseif ($u1['user_date_created'] > $u2['user_date_created']){ return 1; }
  else { return 0; }
}
uasort($contributor_index, 'temp_sort_by_date');
$contributor_ids = array_keys($contributor_index);


// Start generating the page markup
ob_start();
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Credits Overview</h2>

<div class="subbody">

  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/bomb-man/sprite_left_80x80.png);"></div></div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources.  Being a Mega Man fan-game, this project obviously owes most of it's thanks to <a href="http://www.capcom.com/" target="_blank" rel="nofollow">Capcom</a> and of course, Keiji Inafune.  Most of the assets used throughout this website and game were created by Capcom for use in the original games, so their role in this project is far from minor and greatly influential. In addition, their generally positive attitude about fan-games and other types of fan-tribute have been very admirable over the years, and both this game and the Mega Man community owe them much gratitude.</p>

  <div class="float float_left"><div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="background-image: url(images/robots/metal-man/sprite_right_80x80.png);"></div></div>
  <p class="text">Capcom are not the only ones to thank, however, as many others have contributed to this project over the years.  Though most of the actual design and development has been done by Adrian thus far, hours of play-testing, tons of feature ideas, mechanics discussions, and even additional sprite editing has been provided by talented and generous outside sources over the years.  New members are being added to the team all the time, and even the smallest amount of effort is appreciated.  If you would like to help with sprite editing, bug testing, feature ideas, or anything please <a href="contact/">contact me</a> and we'll discuss the details.  You'll be credited appropriately on this page, with a link back to your home page and a custom description if you want them.</p>

</div>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Contributors Index</h2>


<?
// -- CONTRIBUTOR INDEX -- //
foreach ($contributor_ids AS $id){
  $temp_userinfo = $contributor_index[$id];
  if (!empty($temp_userinfo['user_name_public'])){
    $temp_public_hash = preg_replace('/[^a-z0-9]+/i', '', strtolower($temp_userinfo['user_name_public']));
    $temp_base_hash = preg_replace('/[^a-z0-9]+/i', '', strtolower($temp_userinfo['user_name']));
    $temp_displayname = $temp_public_hash != $temp_base_hash ? $temp_userinfo['user_name_public'].' / '.$temp_userinfo['user_name'] : $temp_userinfo['user_name_public'];
  } else {
    $temp_displayname = $temp_userinfo['user_name'];
  }
  $temp_displayname_short = !empty($temp_userinfo['user_name_public']) ? $temp_userinfo['user_name_public'] : $temp_userinfo['user_name'];
  $temp_displayline = !empty($temp_userinfo['user_credit_line']) ? $temp_userinfo['user_credit_line'] : 'Miscellaneous Contributions';
  $temp_displaytext = !empty($temp_userinfo['user_credit_text']) ? $temp_userinfo['user_credit_text'] : $temp_displayname_short.' joined the prototype on '.date('F jS, Y', $temp_userinfo['user_date_created']).' and has since become a contributor.';
  $temp_background = !empty($temp_userinfo['user_background_path']) ? $temp_userinfo['user_background_path'] : 'fields/intro-field';
  $temp_websitelink = !empty($temp_userinfo['user_website_address']) ? $temp_userinfo['user_website_address'] : false;
  $temp_playertype = !empty($temp_userinfo['user_colour_token']) ? $temp_userinfo['user_colour_token'] : 'none';
  $temp_imagepath = !empty($temp_userinfo['user_image_path']) ? $temp_userinfo['user_image_path'] : 'robots/mega-man/40';
  $temp_itemkind = !empty($temp_userinfo['role_icon']) ? $temp_userinfo['role_icon'] : 'energy-pellet';
  list($temp_class, $temp_token, $temp_size) = explode('/', $temp_imagepath);
  ?>
  <div class="subbody" style="position: relative; padding-left: 60px; margin-bottom: 2px; ">
    <div class="float float_left" style="background-position: center center; background-image: url(<?= 'images/'.$temp_background.'/battle-field_avatar.png' ?>); position: absolute; left: 5px; top: 8px; height: 25px; width: 40px; padding-top: 15px;"><div class="sprite sprite_<?= $temp_size.'x'.$temp_size ?> sprite_<?= $temp_size.'x'.$temp_size ?>_02" style="background-image: url(images/<?= $temp_class.'/'.$temp_token.'/' ?>/sprite_right_<?= $temp_size.'x'.$temp_size ?>.png); <?= $temp_size == 80 ? 'margin-left: -22px; margin-top: -60px; ' : '' ?>"><?= $temp_displayname ?></div></div>
    <div class="text">
      <div>
        <a class="type_span type type_<?= $temp_playertype ?>" style="padding: 1px 6px 2px 22px; background: transparent url(images/abilities/item-<?= $temp_itemkind ?>/icon_left_40x40.png) scroll no-repeat -10px -11px; text-decoration: none; " href="leaderboard/<?= $temp_userinfo['user_name_clean'] ?>/"><strong><?= $temp_displayname ?></strong></a>
        <? if(!empty($temp_websitelink)): ?><span class="pipe">|</span> <a style="font-size: 10px;" href="<?= $temp_websitelink ?>" target="_blank" rel="contributor"><?= $temp_websitelink ?></a><? endif;?>
      </div>
      <div><span><?= $temp_userinfo['role_name'] ?></span> <span class="pipe">|</span> <span><?= date('F Y', $temp_userinfo['user_date_created']) ?></span> <span class="pipe">|</span> <em><?= $temp_displayline ?></em></div>
      <?= mmrpg_formatting_decode($temp_displaytext) ?>
    </div>
  </div>
  <?
}
?>
<div class="subbody" style="margin-bottom: 2px; ">
  <p class="text">
    <a href="http://www.youtube.com/user/TheLegendOfRenegade/" target="_blank"><strong>TheLegendOfRenegade</strong></a> <span class="pipe">|</span> <span>Contributor</span> <span class="pipe">|</span> <em>Mega Man 1, 2, 7, and R&amp;F Sega Genesis Remixes</em><br />
    YouTube user TheLegendOfRenegade is responsible for any and all music and sound effects in the prototype and graciously agreed to let us use his work in July 2013.  All tracks come from his massive, multiple-game-spanning Sega Genesis / MD Remix project and they are really, really incredible.  All field music thus far has come from his <a href="http://www.youtube.com/playlist?list=PL0jbwTITrHXaBy3etRm-nmkx-f-QaPOwQ" target="_blank">Mega Man 1 &amp; 2</a> collection with various menu and special battle themes coming from the <a href="http://www.youtube.com/playlist?list=PL0jbwTITrHXZfMogt7ll1ThTlpND_5KZH" target="_blank">Rockman &amp; Forte</a> and <a href="http://www.youtube.com/playlist?list=PL0jbwTITrHXbA31nYgKs863E4UgLxUJkh" target="_blank">Mega Man 7</a> collections in addition to the former.  The level of quality and polish these remixes bring to the game is beyond words, and I am so grateful that we're able to showcase them in our prototype.  TheLegendOfRenegade has done some truly great work, and I think I speak for everyone on the team when I say &quot;thank you&quot;.  ^_^
    <span style="display: block; font-size: 10px; line-height: 12px; margin-top: 2px;">* Because TheLegendOfRenegade has not created remixes for Time Man and Oil Man's stages in Powered Up, the R&amp;F themes for Astro Man and Tengu Man are currently being used in their place. The game will be updated if/when he ever creates them, but please enjoy the current selection until then. :)</span>
  </p>
</div>
<div class="subbody">
  <p class="text">
    <strong>PaRcoO</strong> <span class="pipe">|</span> <span>Contributor</span> <span class="pipe">|</span> <em>Game Testing / Bug Tracking, Game / Feature Ideas &amp; Discussion</em><br />
    PaRcoO starting playing and contributing in December 2012 and has since offered a much assistance with bug tracking, feature ideas, and a great deal of time play-testing.  We all appreciate your contributions very much - thank you! :)
  </p>
</div>
<?/*
<div class="subbody">
  <p class="text">
    <a href="https://www.youtube.com/user/bsolmaz13/" target="_blank"><strong>bsolmaz13</strong></a> <span class="pipe">|</span> <span>Contributor</span> <span class="pipe">|</span> <em>Time Man / Oil Man Stage Music</em><br />
    YouTube user bsolmaz13 created the 8-bit music heard in the Clock Citadel and Oil Wells stages in January and March of 2012, respectively, and have been used in-game since January 2013.  Because those two stages never existed in the original NES game, 8-bit music does not exist for them in an official capacity - thus, bsolmaz13 created amazing remixes for both <a href="https://www.youtube.com/watch?v=Oyvu_CSrE90" target="_blank">Time Man</a> and <a href="https://www.youtube.com/watch?v=a9RFAELi-aY" target="_blank">Oil Man</a>'s stages.  Thank you so much for these awesome tracks!
  </p>
</div>
*/?>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>