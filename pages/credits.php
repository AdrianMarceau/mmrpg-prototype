<?
/*
 * INDEX PAGE : CREDITS
 */

// Define the SEO variables for this page
$this_seo_title = 'Credits | '.$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources. Being a Mega Man fan-game, this project obviously owes most of it\'s thanks to Capcom and of course, Keiji Inafune. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Credits and Contributors';
$this_graph_data['description'] = 'The Mega Man RPG Prototype was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources. Being a Mega Man fan-game, this project obviously owes most of it\'s thanks to Capcom and of course, Keiji Inafune.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Credits & Contributors';

// Collect user data for all contributors in the database
$contributor_ids = array(
    412,  // AdrianMarceau (Developer)
    // 92,  // ChillPenguin (Administrator)
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
$contributor_index = $db->get_array_list("SELECT * FROM mmrpg_users LEFT JOIN mmrpg_roles ON mmrpg_users.role_id = mmrpg_roles.role_id WHERE user_id IN (".implode(', ', $contributor_ids).")", 'user_id');
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

// Collect the elemental types index
$mmrpg_types_index = rpg_type::get_index(true);


// Start generating the page markup
ob_start();
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Credits Overview</h2>

<div class="subbody">

    <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/bomb-man/sprite_left_80x80.png);">Adrian Marceau</div></div>
    <p class="text">The <strong>Mega Man RPG Prototype</strong> was created and is continually developed and maintained by Adrian Marceau / Ageman20XX, though the project would not have been possible without a great deal of inspiration and contributions from multiple outside sources.  Being a Mega Man fan-game, this project obviously owes most of it's thanks to <a href="http://www.capcom.com/" target="_blank" rel="nofollow">Capcom</a> and of course, Keiji Inafune.  Most of the assets used throughout this website and game were created by Capcom for use in the original games, so their role in this project is far from minor and greatly influential. In addition, their generally positive attitude about fan-games and other types of fan-tribute have been very admirable over the years, and both this game and the Mega Man community owe them much gratitude.</p>

    <div class="float float_left"><div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/metal-man/sprite_right_80x80.png);">Metal Man</div></div>
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
    $temp_role_id = !empty($temp_userinfo['role_id']) ? $temp_userinfo['role_id'] : 3;
    $temp_displayname_short = !empty($temp_userinfo['user_name_public']) ? $temp_userinfo['user_name_public'] : $temp_userinfo['user_name'];
    $temp_displayline = !empty($temp_userinfo['user_credit_line']) ? $temp_userinfo['user_credit_line'] : 'Miscellaneous Contributions';
    $temp_displaytext = !empty($temp_userinfo['user_credit_text']) ? $temp_userinfo['user_credit_text'] : $temp_displayname_short.' joined the prototype on '.date('F jS, Y', $temp_userinfo['user_date_created']).' and has since become a contributor.';
    $temp_background = !empty($temp_userinfo['user_background_path']) ? $temp_userinfo['user_background_path'] : 'fields/intro-field';
    $temp_websitelink = !empty($temp_userinfo['user_website_address']) ? $temp_userinfo['user_website_address'] : false;
    $temp_playertype = !empty($temp_userinfo['user_colour_token']) ? $temp_userinfo['user_colour_token'] : 'none';
    //$temp_textcolour = !in_array($temp_playertype, array('empty', 'shadow')) ? 'rgb('.implode(', ', $mmrpg_types_index[$temp_playertype]['type_colour_light']).')' : 'rgb(97, 97, 97)';
    $temp_imagepath = !empty($temp_userinfo['user_image_path']) ? $temp_userinfo['user_image_path'] : 'robots/mega-man/40';
    $temp_itemkind = !empty($temp_userinfo['role_icon']) ? $temp_userinfo['role_icon'] : 'energy-pellet';
    list($temp_class, $temp_token, $temp_size) = explode('/', $temp_imagepath);
    ?>
    <div class="subbody creditblock">
        <div class="float float_left" style="background-image: url(<?= 'images/'.$temp_background.'/battle-field_avatar.png' ?>);"><div class="sprite sprite_<?= $temp_size.'x'.$temp_size ?> sprite_<?= $temp_size.'x'.$temp_size ?>_02" style="background-image: url(images/<?= $temp_class.'/'.$temp_token.'/' ?>/sprite_right_<?= $temp_size.'x'.$temp_size ?>.png); <?= $temp_size == 80 ? 'margin-left: -22px; margin-top: -60px; ' : '' ?>"><?= $temp_displayname ?></div></div>
        <div class="text ">
            <div class="details">

                <div class="name">
                    <a class="link_inline player_type player_type_<?= $temp_playertype ?>" style="background-image: url(images/items/<?= $temp_itemkind ?>/icon_left_40x40.png)" href="leaderboard/<?= $temp_userinfo['user_name_clean'] ?>/"><strong><?= $temp_displayname ?></strong></a>
                </div>

                <div class="title">
                    <span class="label">Title :</span>
                    <?= $temp_userinfo['role_name_full'] ?>
                </div>

                <div class="credits">
                    <span class="label">Credits :</span>
                    <em class="reason"><?= $temp_displayline ?></em>
                </div>

                <div class="date">
                    <span class="label">Tenure :</span>
                    <span class="time"><?
                        $d1 = new DateTime(date('Y-m-d', $temp_userinfo['user_date_created']));
                        $d2 = new DateTime(date('Y-m-d', $temp_userinfo['user_last_login']));
                        $diff = $d2->diff($d1);
                        $yyyy = $diff->y;
                        $mm = $diff->m;
                        echo $yyyy.' Years'.(!empty($mm) ? ', '.$mm.' Months' : '');
                        ?></span>
                        (<span class="from"><?= date('F Y', $temp_userinfo['user_date_created']) ?></span> to
                        <span class="to"><?= date('F Y', $temp_userinfo['user_last_login']) ?></span>)
                </div>

                <? if(!empty($temp_websitelink)): ?>
                    <div class="website">
                        <span class="label">Website :</span>
                        <a class="link_inline" href="<?= $temp_websitelink ?>" target="_blank" rel="contributor"><?= $temp_websitelink ?></a>
                    </div>
                <? endif;?>

            </div>

            <div class="text description">
                <?= str_replace('<br /><br />', '</div><div>', mmrpg_formatting_decode($temp_displaytext)) ?>
            </div>

        </div>
    </div>
    <?
}
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Other Contributors</h2>
<div class="subbody creditblock compact">
    <div class="text">
        <div class="detail">
            <a class="name" href="http://www.youtube.com/user/TheLegendOfRenegade/" target="_blank"><strong>TheLegendOfRenegade</strong></a>
            <span class="pipe">|</span>
            <span class="title">Contributor</span>
            <span class="pipe">|</span>
            <em class="reason">Mega Man Sega Genesis Remixes</em>
        </div>
        <div class="text description">
            <p>YouTube user TheLegendOfRenegade is responsible for any and all music and sound effects in the prototype and graciously agreed to let us use his work in July 2013.  All tracks come from his massive, multiple-game-spanning Sega Genesis / MD Remix project and they are really, really incredible.  All field music thus far has come from his <a href="http://www.youtube.com/playlist?list=PL0jbwTITrHXaBy3etRm-nmkx-f-QaPOwQ" target="_blank">Mega Man 1 - 4</a> collection with various menu and special battle themes coming from the <a href="http://www.youtube.com/playlist?list=PL0jbwTITrHXZfMogt7ll1ThTlpND_5KZH" target="_blank">Rockman &amp; Forte</a> and <a href="http://www.youtube.com/playlist?list=PL0jbwTITrHXbA31nYgKs863E4UgLxUJkh" target="_blank">Mega Man 7</a> collections in addition to the former.  The level of quality and polish these remixes bring to the game is beyond words, and I am so grateful that we're able to showcase them in our prototype.  TheLegendOfRenegade has done some truly great work, and I think I speak for everyone on the team when I say &quot;thank you&quot;.  ^_^</p>
            <p class="subtext">* Because TheLegendOfRenegade has not created remixes for Time Man and Oil Man's stages in Powered Up, the R&amp;F themes for Astro Man and Tengu Man are currently being used in their place. The game will be updated if/when he ever creates them, but please enjoy the current selection until then. :)</p>
        </div>
    </div>
</div>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Resources Index</h2>
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
        All MP3 tracks were converted to Firefox-compatible OGG files using this tool, and it has been incredibly helpful in easing the pain of cross-browser support.  Their online audio conversion is very simple to use and is completely free.  I am so happy that this tool exists and recommend it to anyone interested in HTML game development.
    </p>
</div>



<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Honorable Mentions</h2>
<div class="subbody creditblock compact">
    <div class="text">
        <div class="detail">
            <strong class="name">MegaBoyX7</strong>
            <span class="pipe">|</span>
            <em class="reason">Chat Room Emoticons</em>
        </div>
        <div class="description">
            <p>MegaBoyX7 starting playing in November 2014 but didn't start contributing until April 2016 when he created a fantastic set of Mega Man themed icons for our chat room! Minor edits were implemented by myself and MegaBossMan, but the base sprites and the idea to change them came entirely from MegaBoyX7.  Thank you!</p>
        </div>
    </div>
</div>
<div class="subbody creditblock compact">
    <div class="text">
        <div class="detail">
            <strong class="name">PaRcoO</strong>
            <span class="pipe">|</span>
            <em class="reason">Game Testing / Bug Tracking, Game / Feature Ideas &amp; Discussion</em>
        </div>
        <div class="description">
            <p>PaRcoO starting playing and contributing in December 2012 and has since offered a much assistance with bug tracking, feature ideas, and a great deal of time play-testing.  We all appreciate your contributions very much - thank you! :)</p>
        </div>
    </div>
</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>