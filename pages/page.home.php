<?
/*
 * INDEX PAGE : HOME
 */

// Define the SEO variables for this page
$this_seo_title = $this_seo_title; //Home | '.$this_seo_title;
$this_seo_description = 'Welcome to the Mega Man RPG Prototype, where you can battle your way through more than sixteen robot masters in classic RPG style with either Dr. Light and Mega Man or Dr. Wily and Proto Man!';

// Define the Open Graph variables for this page
//$this_graph_data['title'] = 'Mega Man RPG Prototype';
$this_graph_data['description'] = 'Welcome to the Mega Man RPG Prototype, where you can battle your way through more than sixteen robot masters in classic RPG style with either Dr. Light and Mega Man or Dr. Wily and Proto Man!';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Developer Build <span class="count count_header">( Last Updated '.preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE).' )</span>';

// Start generating the page markup
ob_start();
?>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><span class="subheader_typewrapper">What is the Mega Man RPG Prototype?</span></h2>
<div class="subbody" style="margin-bottom: 2px;">
  <?/*
  <p class="text" style="font-size: 12px; line-height: 18px; padding: 2px 0 6px; letter-spacing: 1px; text-align: justify; color: #66FF51; border-bottom: 1px dotted #292929;">
    Notice : Code has been optimized and memory leaks should now be fixed, so the MMRPG Prototype is finally able to come back online! Everything <em>should</em> be back to normal now and hopefully a lot more stable, but if you find any new bugs please <a href="community/bugs/0/new/" style="color: #66FF51;">post them in a new topic</a> on the forum to help us debug and fix any lingering issues. Apologies for the inconvenience and thank you once again for playing and for your support!
  </p>
  <p class="text" style="font-size: 12px; line-height: 18px; padding: 2px 0 6px; letter-spacing: 1px; text-align: justify; color: #EC3131; border-bottom: 1px dotted #292929;">
    Notice : Due to excessive server load and memory exhaustion the prototype has been taken offline again.  I really tried my best to optimize the code and comply with my web host's requests for a less resource-intensive script but it may be beyond my abilities.  It feels like the more I try to fix it the worse it gets, and I unfortunately do not have any idea when these issues will be resolved.  Please discuss the outage on the forums if you need to, and know that I am very sorry for all the trouble and seemingly wasted time on this project.  I will update the website when I have more information on the project's future, but for now consider the game on hiatus.  :(
  </p>
  <p class="text" style="font-size: 16px; line-height: 22px; padding: 4px 0 12px; letter-spacing: 1px; text-align: justify; color: #ECA931; border-bottom: 1px dotted #292929;">
    (!) The prototype is currently offline and in the process of moving to a new server.  We should be back online in a few days and you'll be able to pick up right where you left off.  Thank you for your patience, and see you on the other side! :D
  </p>
  <p class="text" style="font-size: 16px; line-height: 22px; padding: 4px 0 12px; letter-spacing: 1px; text-align: justify; color: #51B618; border-bottom: 1px dotted #292929;">
    (!) The prototype is back online and (as far as we can tell) the process of moving to a new server has been completed!  All save files and posts have been migrated over to the new hosting and you'll be able to pick up right where you left off.  Thank you for your patience, and please <a href="community/bugs/" style="color: #51B618; text-decoration: none;">let us know</a> if we broke anything in the move! :P
  </p>
  */?>
  <p class="text" style="font-size: 14px; line-height: 23px; padding: 2px 0 6px; letter-spacing: 1px; text-align: justify; color: rgb(157, 220, 255);">
    After a freak accident Dr. Light and Mega Man find themselves digitized, separated from Dr. Cossack, and trapped in a prototype battle simulator!
    The two heroes are forced to fight for their lives as they search for their friend and a way back to the real world!
    Challenge powered-up copies of past robots to battle and download their data to become stronger and escape from the system!
  </p>
</div>
<div class="subbody" style="position: relative; ">
  <?/*<div id="youtube_playlist" style="">
    <iframe width="356" height="200" src="//www.youtube.com/embed/videoseries?list=PL2yhjPks7HSo_vJNq02ls_DwhmrA2pmU5" frameborder="0" allowfullscreen></iframe>
  </div>*/?>
  <p class="text">
    <span class="float float_right"><span class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png);">Mega Man</span></span>
    The <strong>Mega Man RPG Prototype</strong> is an ongoing fangame project with the goal of creating a progress-saving, no-download, no-install, cross-platform, browser-based Mega Man RPG (or what some would call a <a href="http://www.pbbg.org/" target="_blank" rel="related">PBBG</a>) that combines the addictive collection and battle mechanics of the Pokémon series with the beloved robots and special weapons of the classic Mega Man series. Fight your way through more than fifty different robot masters in a turn-based battle system reminiscent of both play-by-post forum games and early 8-bit role-playing games.
  </p>
  <p class="text">
    <span class="float float_left"><span class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/robots/proto-man/sprite_right_80x80.png);">Proto Man</span></span>
    This project is a labour of love and a massive work-in-progress, but you can always <a href="prototype/">play the game online</a> by clicking the link in the menu above.  Play through the demo missions or create a new account to gain access to the full game with save functionality, 40 unique battle fields, over 1000 dynamically generated missions, 3 playable characters, 38 unlockable robots, 92 abilities, and tons of fun new features and little extras. Additional information about the development of the game can be found on the <a href="about/">About</a>, <a href="credits/">Credits</a>, and <a href="database/">Database</a> pages, and discussions about the game can be found on the <a href="community/">Community</a> pages.  If you have any feedback or questions about the project please <a href="contact/">contact me</a> for more information.
  </p>
  <?/*
  <div id="facebook_badge" style="position: absolute; top: 7px; right: 7px; width: 120px; height: 206px; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; overflow: hidden;">
    <a href="https://www.facebook.com/megamanrpgprototype" target="_TOP" title="Mega Man RPG Prototype"><img src="https://badge.facebook.com/badge/493570630708693.2157.45298751.png" style="border: 0px;  border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; overflow: hidden;" /></a>
    <span style="display: block; position: relative; top: -212px; width: 120px; height: 212px; box-shadow: inset 1px 1px 5px #000000; pointer-events: none;">&nbsp;</span>
  </div>
  */?>
</div>

<?
// Require the leaderboard data for display
$this_display_limit_default = 3;
require('data/leaderboard.php');
?>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
  <span class="subheader_typewrapper">
    <a class="link" href="leaderboard/">Mega Man RPG Prototype Leaderboard</a>
    <span class="count">( <?= (!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : '0 Players').($this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '') ?> )</span>
    <a class="float_link" href="leaderboard/">View More Players &raquo;</a>
  </span>
</h2>
<div class="leaderboard" style="margin-bottom: 12px; overflow: visible;">
  <div class="wrapper" style="margin-bottom: 0; overflow: visible;">
  <?

  // Define the leaderboard displauy limit
  $leaderboard_display_limit = $this_display_limit_default;
  // Print out the generated leaderboard markup
  if (!empty($this_leaderboard_markup)){
    foreach ($this_leaderboard_markup AS $key => $leaderboard_markup){
      // If we're over the limit, break
      if ($key >= $leaderboard_display_limit){ break; }
      // Display this leaderboard image's markup
      echo $leaderboard_markup;
    }
    unset($this_leaderboard_markup);
  }

  ?>
  </div>
</div>

<?
// Require the gallery data for display
require_once('data/gallery.php');
?>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
  <span class="subheader_typewrapper">
    <a class="link" href="gallery/">Mega Man RPG Prototype Gallery</a>
    <span class="count">( <?= !empty($this_file_count) ? ($this_file_count == 1 ? '1 Image' : $this_file_count.' Images') : '0 Images' ?> )</span>
    <a class="float_link" href="gallery/">View More Images &raquo;</a>
  </span>
</h2>
<div class="gallery" style="margin-bottom: 12px;">
  <div class="wrapper" style="margin-bottom: 0;">
  <?

  // Define the gallery displauy limit
  $gallery_display_limit = 16;
  // Print out the generated gallery markup
  if (!empty($this_gallery_markup)){
    foreach ($this_gallery_markup AS $key => $gallery_markup){
      // If we're over the limit, break
      if ($key >= $gallery_display_limit){ break; }
      // Display this gallery image's markup
      echo $gallery_markup;
    }
    unset($this_gallery_markup);
  }

  ?>
  </div>
</div>

<?

// Collect all the threads for this category from the database
$this_category_id = 1; // News
$this_threads_query = "SELECT threads.*, users.*, users2.*, users3.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
  LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
  LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_name_clean AS mod_user_name_clean, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
  LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_name_clean AS target_user_name_clean, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
  LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
  LEFT JOIN (
  SELECT posts.thread_id, count(1) AS post_count
  FROM mmrpg_posts AS posts WHERE posts.post_deleted = 0
  GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
  WHERE threads.category_id = {$this_category_id} AND threads.thread_published = 1
  ORDER BY threads.thread_sticky DESC, threads.thread_date DESC";
$this_threads_array = $DB->get_array_list($this_threads_query);
$this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;
$this_category_info = $DB->get_array("SELECT * FROM mmrpg_categories WHERE category_id = {$this_category_id}");

?>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
  <span class="subheader_typewrapper">
    <a class="link" href="community/news/">Mega Man RPG Prototype News &amp; Updates</a>
    <span class="count">( <?= !empty($this_threads_count) ? ($this_threads_count == 1 ? '1 Post' : $this_threads_count.' Posts') : '0 Posts' ?> )</span>
    <a class="float_link" href="community/news/">View More Posts &raquo;</a>
  </span>
</h2>
<div class="community" style="margin-bottom: 12px;">
  <?

  // Define the current date group
  $this_date_group = '';
  $this_date_group_count = 0;

  // Define the temporary timeout variables
  $this_time = time();
  $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

  // Loop through the thread array and display its contents
  if (!empty($this_threads_array)){
    foreach ($this_threads_array AS $this_thread_key => $this_thread_info){

      // If this thread is over the display limit, break from the loop
      if ($this_thread_key >= MMRPG_SETTINGS_THREADS_RECENT){ break; }

      // Collect markup for this thread from the function
      $temp_markup = mmrpg_website_community_thread_linkblock($this_thread_info, $this_category_info, 'compact');
      echo $temp_markup."\n";

    }
  }

  ?>
</div>
<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>