<?
/*
 * COMMUNITY THREAD VIEW
 */

// Define the temporary timeout variables
$this_time = time();
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

// If this is a PERSONAL thread, we have to do some security
if ($this_category_info['category_token'] == 'personal'){
  // Ensure the user is logged in, else redirect to login
  if ($_SESSION['GAME']['USER']['userid'] == MMRPG_SETTINGS_GUEST_ID){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'file/load/');
    exit();
  }
  // Ensure the user is actually part of the thread, else redirect to community index
  elseif ($_SESSION['GAME']['USER']['userid'] != $this_thread_info['user_id']
    && $_SESSION['GAME']['USER']['userid'] != $this_thread_info['thread_target']){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'community/personal/');
    exit();
  }
}

// Update the SEO variables for this page
$this_seo_title = $this_thread_info['thread_name'].' | '.$this_category_info['category_name'].' | '.$this_seo_title;
$this_seo_description = strip_tags(mmrpg_formatting_decode($this_thread_info['thread_body']));
if (strlen($this_seo_description) > 200){ $this_seo_description = substr($this_seo_description, 0, 200).'...'; }

// Define the Open Graph variables for this page
$this_graph_data['title'] = $this_category_info['category_name'].' Discussions | '.$this_thread_info['thread_name'];
$this_graph_data['description'] = strip_tags(mmrpg_formatting_decode($this_thread_info['thread_body']));
if (strlen($this_graph_data['description']) > 200){ $this_graph_data['description'] = substr($this_graph_data['description'], 0, 200).'...'; }
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE;
$this_graph_data['type'] = 'article';
$this_graph_data['article__published_time'] = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
$this_graph_data['article__published_time'] = date('Y-m-d', $this_graph_data['article__published_time']).'T'.date('H:i', $this_graph_data['article__published_time']);
$this_graph_data['article__modified_time'] = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
$this_graph_data['article__modified_time'] = date('Y-m-d', $this_graph_data['article__modified_time']).'T'.date('H:i', $this_graph_data['article__modified_time']);
$this_graph_data['article__author'] = !empty($this_thread_info['user_name_public']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];

/*
article:published_time - datetime - When the article was first published.
article:modified_time - datetime - When the article was last changed.
article:expiration_time - datetime - When the article is out of date after.
article:author - profile array - Writers of the article.
article:section - string - A high-level section name. E.g. Technology
article:tag - string array - Tag words associated with this article.
*/

// Update the MARKUP variables for this page
//$this_markup_header = $this_thread_info['thread_name']; //.' | '.$this_markup_header;

// Define this thread's session tracker token
$thread_session_token = $this_thread_info['thread_id'].'_';
$thread_session_token .= !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];
// Check if this thread has already been viewed this session
$thread_session_viewed = in_array($thread_session_token, $_SESSION['COMMUNITY']['threads_viewed']) ? true : false;

// Check to see if this is a message thread, and then if being viewed by creator
$is_personal_message = $this_thread_info['thread_target'] != 0 ? true : false;
$is_personal_message_creator = $is_personal_message && $this_thread_info['user_id'] == $this_userinfo['user_id'] ? true : false;

// Collect any posts for this specific thread from the database
$this_posts_query = "SELECT posts.*,
  users.*,
  roles.*
	FROM mmrpg_posts AS posts
	LEFT JOIN mmrpg_users AS users ON posts.user_id = users.user_id
	LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
	WHERE posts.thread_id = '{$this_thread_info['thread_id']}'
	ORDER BY posts.post_date ASC";
$this_posts_array = $DB->get_array_list($this_posts_query);
if (empty($this_posts_array)){ $this_posts_array = array(); }
$this_posts_count = !empty($this_posts_array) ? count($this_posts_array) : 0;
//$this_posts_count = $DB->get_value("SELECT COUNT(1) AS post_count FROM mmrpg_posts AS posts WHERE posts.thread_id = '{$this_thread_info['thread_id']}' AND posts.post_deleted = 0", 'post_count');

// Define the array of user ids to collect information for
$temp_user_ids = array();
if (!empty($this_thread_info['user_id'])){ $temp_user_ids[] = $this_thread_info['user_id']; }
if (!empty($this_thread_info['thread_target'])){ $temp_user_ids[] = $this_thread_info['thread_target']; }
foreach ($this_posts_array AS $key => $array){
  if ($is_personal_message && $array['user_id'] != $this_userinfo['user_id'] && $array['post_target'] != $this_userinfo['user_id']){ unset($this_posts_array[$key]); }
  if (!empty($array['user_id'])){ $temp_user_ids[] = $array['user_id'];  }
  if (!empty($array['post_target'])){ $temp_user_ids[] = $array['post_target'];  }
} $temp_user_ids = array_unique($temp_user_ids);
$this_posts_array = array_values($this_posts_array);
$this_posts_count = !empty($this_posts_array) ? count($this_posts_array) : 0;


// If the current post count is somehow higher than the view count, fix it up
if ($this_posts_count >= $this_thread_info['thread_views']){
  $this_thread_info['thread_views'] += $this_posts_count;
  $DB->query("UPDATE mmrpg_threads SET thread_views = {$this_thread_info['thread_views']} WHERE thread_id = {$this_thread_info['thread_id']}");
}

// Collect the thread counts for all users in an index
$this_user_countindex = $DB->get_array_list('SELECT
  mmrpg_users.user_id,
  mmrpg_leaderboard.board_points,
  mmrpg_threads.thread_count,
  mmrpg_posts.post_count
  FROM mmrpg_users
  LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_users.user_id
  LEFT JOIN (
  SELECT user_id, COUNT(thread_id) AS thread_count FROM mmrpg_threads WHERE mmrpg_threads.thread_target = 0 AND thread_published = 1 GROUP BY mmrpg_threads.user_id
  ) mmrpg_threads ON mmrpg_threads.user_id = mmrpg_users.user_id
  LEFT JOIN (
  SELECT user_id, COUNT(post_id) AS post_count FROM mmrpg_posts WHERE mmrpg_posts.post_target = 0 AND post_deleted = 0 GROUP BY mmrpg_posts.user_id
  ) mmrpg_posts ON mmrpg_posts.user_id = mmrpg_users.user_id
  WHERE mmrpg_leaderboard.board_points > 0 AND mmrpg_users.user_id IN ('.implode(', ', $temp_user_ids).')', 'user_id');

// Define the temporary display variables
$temp_thread_guest = $this_thread_info['user_id'] == MMRPG_SETTINGS_GUEST_ID ? true : false;
$temp_thread_name = $this_thread_info['thread_name'];
$temp_thread_author = !empty($this_thread_info['user_name_public']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];
$temp_thread_date = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
$temp_thread_date = date('F jS, Y', $temp_thread_date).' at '.date('g:ia', $temp_thread_date);
$temp_thread_body = $this_thread_info['thread_body'];
$temp_thread_views = !empty($this_thread_info['thread_views']) ? $this_thread_info['thread_views'] : 0;

// If this is a PM, collect the target's info
if ($is_personal_message){
  $temp_thread_targetinfo = $DB->get_array("SELECT user_id, user_name, user_name_public, user_name_clean FROM mmrpg_users WHERE user_id = {$this_thread_info['thread_target']} LIMIT 1");
  $temp_thread_target = !empty($temp_thread_targetinfo['user_name_public']) ? $temp_thread_targetinfo['user_name_public'] : $temp_thread_targetinfo['user_name'];
}

// Define the avatar class and path variables
$temp_avatar_frame = !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
$temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
$temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/intro-field';
list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
$temp_avatar_size = $temp_avatar_size * 2;
list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
$temp_avatar_class = 'avatar avatar_80x80 float float_'.($is_personal_message_creator ? 'left' : 'right').' ';
$temp_avatar_colour = !empty($this_thread_info['user_colour_token']) ? $this_thread_info['user_colour_token'] : 'none';
$temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
$temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.($is_personal_message_creator ? 'right' : 'left').'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
$temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
$temp_is_contributor = in_array($this_thread_info['role_token'], array('developer', 'administrator', 'contributor', 'moderator')) ? true : false;
if ($temp_is_contributor){
  $temp_item_class = 'sprite sprite_80x80 sprite_80x80_00';
  $temp_item_path = 'images/abilities/item-'.(!empty($this_thread_info['role_icon']) ? $this_thread_info['role_icon'] : 'energy-pellet' ).'/icon_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
  $temp_item_title = !empty($this_thread_info['role_name']) ? $this_thread_info['role_name'] : 'Contributor';
}

// Check if the thread creator is currently online
$temp_is_online = false;
$temp_leaderboard_online = mmrpg_prototype_leaderboard_online();
foreach ($temp_leaderboard_online AS $key => $info){ if ($info['id'] == $this_thread_info['user_id']){ $temp_is_online = true; break; } }

// Collect the thread count for this user
if ($this_thread_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_thread_info['thread_count'] = !empty($this_user_countindex[$this_thread_info['user_id']]['thread_count']) ? $this_user_countindex[$this_thread_info['user_id']]['thread_count'] : 0; }
else { $this_thread_info['thread_count'] = false; }
// Collect the thread count for this user
if ($this_thread_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_thread_info['post_count'] = !empty($this_user_countindex[$this_thread_info['user_id']]['post_count']) ? $this_user_countindex[$this_thread_info['user_id']]['post_count'] : 0; }
else { $this_thread_info['post_count'] = false; }

// Define the base URL for this community thread, sans page number
$this_thread_url = preg_replace('/([0-9]+)\/?$/i', '', $this_current_uri);
//die($this_thread_url);

// Define how many posts should appear per page, and calculate related details
$this_posts_perpage = MMRPG_SETTINGS_POSTS_PERPAGE;
$this_pages_count = $this_posts_count > 0 ? ceil($this_posts_count / $this_posts_perpage) : 1;
$this_page_current = !empty($this_current_num) ? $this_current_num : 1;
if ($this_page_current > $this_pages_count){ $this_page_current = $this_pages_count; }
elseif ($this_page_current < 1){ $this_page_current = 1; }
$this_page_maxpost_key = ($this_page_current * $this_posts_perpage) - 1;
$this_page_minpost_key = $this_page_maxpost_key - ($this_posts_perpage - 1);

//die('<pre>'.print_r($this_thread_info, true).'</pre>');

?>
<h2 class="subheader thread_name field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="">
  <span class="thread_namewrapper" style="">
    <a class="link" style="" href="<?= str_replace($this_category_info['category_token'].'/'.$this_current_id.'/'.$this_current_token.'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="" href="<?= str_replace($this_current_id.'/'.$this_current_token.'/', '', $_GET['this_current_url']) ?>"><?= $this_category_info['category_name'] ?></a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="" href="<?= $_GET['this_current_url'] ?>" title="<?= $temp_thread_name ?>"><?= $temp_thread_name ?></a>
  </span>
  <span style="float: right; opacity: 0.50;"><?= $temp_thread_date ?></span>
</h2>
<? if ($this_page_current == 1){ ?>
  <div class="subbody thread_subbody thread_subbody_full thread_subbody_full_<?= $is_personal_message_creator ? 'left' : 'right' ?> thread_<?= $is_personal_message_creator ? 'left' : 'right' ?>" style="text-align: left; position: relative; padding-bottom: 60px;">

    <? if($is_personal_message_creator): ?>

      <div data-user="<?= $this_thread_info['user_id'] ?>" class="<?= $temp_avatar_class ?> avatar_fieldback player_type_<?= $temp_avatar_colour ?>" style="border-width: 1px;">
        <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 100px 100px;">
          &nbsp;
        </div>
      </div>
      <div class="<?= $temp_avatar_class ?> avatar_userimage">
        <?/*<div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/mega-man/sprite_left_40x40.png);"><?= $temp_thread_author ?></div>*/?>
        <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); position: absolute; top: -22px; left: -30px;" title="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
        <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_thread_author ?></div>
        <div class="userblock">
          <?= !$temp_thread_guest ? '<a href="leaderboard/'.$this_thread_info['user_name_clean'].'/">' : '' ?>
          <strong data-tooltip-type="player_type player_type_<?= $temp_avatar_colour ?>" title="<?= $temp_thread_author.($temp_is_contributor ? ' | '.$temp_item_title : ' | Player').($temp_is_online ? ' | Online' : '') ?>" class="name thread_username"><?= $temp_thread_author ?></strong>
          <?= !$temp_thread_guest ? '</a>' : '' ?>
          <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['board_points']) ? $this_user_countindex[$this_thread_info['user_id']]['board_points'] : 0; ?>
          <div class="counter points_counter"><?= number_format($temp_stat, 0, '.', ',').' BP' ?></div>
          <div class="counter community_counters">
            <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['thread_count']) ? $this_user_countindex[$this_thread_info['user_id']]['thread_count'] : 0; ?>
            <span class="thread_counter"><?= $temp_stat.' TP' ?></span> <span class="pipe">|</span>
            <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['post_count']) ? $this_user_countindex[$this_thread_info['user_id']]['post_count'] : 0; ?>
            <span class="post_counter"><?= $temp_stat.' PP' ?></span>
          </div>
        </div>
      </div>

    <? else: ?>

      <div data-user="<?= $this_thread_info['user_id'] ?>" class="<?= $temp_avatar_class ?> avatar_fieldback player_type_<?= $temp_avatar_colour ?>" style="border-width: 1px;">
        <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 100px 100px;">
          &nbsp;
        </div>
      </div>
      <div class="<?= $temp_avatar_class ?> avatar_userimage">
        <?/*<div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/mega-man/sprite_left_40x40.png);"><?= $temp_thread_author ?></div>*/?>
        <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); position: absolute; top: -22px; right: -30px;" title="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
        <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_thread_author ?></div>
        <div class="userblock">
          <?= !$temp_thread_guest ? '<a href="leaderboard/'.$this_thread_info['user_name_clean'].'/">' : '' ?>
          <strong data-tooltip-type="player_type player_type_<?= $temp_avatar_colour ?>" title="<?= $temp_thread_author.($temp_is_contributor ? ' | '.$temp_item_title : ' | Player').($temp_is_online ? ' | Online' : '') ?>" class="name thread_username"><?= $temp_thread_author ?></strong>
          <?= !$temp_thread_guest ? '</a>' : '' ?>
          <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['board_points']) ? $this_user_countindex[$this_thread_info['user_id']]['board_points'] : 0; ?>
          <div class="counter points_counter"><?= number_format($temp_stat, 0, '.', ',').' BP' ?></div>
          <div class="counter community_counters">
            <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['thread_count']) ? $this_user_countindex[$this_thread_info['user_id']]['thread_count'] : 0; ?>
            <span class="thread_counter"><?= $temp_stat.' TP' ?></span> <span class="pipe">|</span>
            <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['post_count']) ? $this_user_countindex[$this_thread_info['user_id']]['post_count'] : 0; ?>
            <span class="post_counter"><?= $temp_stat.' PP' ?></span>
          </div>
        </div>
      </div>

    <? endif; ?>

    <div class="bodytext"><?= mmrpg_formatting_decode($temp_thread_body) ?></div>
    <? if((COMMUNITY_VIEW_MODERATOR || $this_userinfo['user_id'] == $this_thread_info['user_id']) && $this_thread_info['category_id'] != 0): ?>
      <? if($this_thread_info['thread_target'] == 0): ?>
      <div class="published" style="position: absolute; bottom: 10px; right: 10px;">
        <?/*<strong><?= $temp_thread_author ?></strong> on <strong><?= $temp_thread_date ?></strong>*/?>
          <span class="options">[ <a class="edit" rel="noindex,nofollow" href="<?= $_GET['this_current_url'].'action=edit&amp;thread_id='.$this_thread_info['thread_id'].'#discussion-form' ?>">edit</a> ]</span>
      </div>
      <? endif; ?>
    <? endif; ?>
    <div class="viewed" style="position: absolute; bottom: 12px; left: 14px; right: 14px; font-size: 10px; line-height: 13px; color: #565656; text-shadow: 0 0 0 transparent; border-top: 1px solid #252424; padding-top: 6px; width: 90%; ">
      <?
      // If this is a personal message, only display the time
      if ($this_category_info['category_id'] == 0){ echo 'Sent by '.$temp_thread_author.' to '.$temp_thread_target.' on '.$temp_thread_date; }
      // Otherwise display extended details about the post
      else { echo $temp_thread_name.'<br /> Posted by '.$temp_thread_author.' on '.$temp_thread_date.'<br /> '.($temp_thread_views == 1 ? 'Viewed 1 Time' : 'Viewed '.$temp_thread_views.' Times'); }
      ?>
    </div>

  </div>
<? } else { ?>
  <div class="subbody thread_subbody thread_subbody_full thread_subbody_full_<?= $is_personal_message_creator ? 'left' : 'right' ?> thread_<?= $is_personal_message_creator ? 'left' : 'right' ?>" style="text-align: left; position: relative; padding-bottom: 60px; min-height: 10px;">
    <div class="bodytext" style="padding-right: 0;">
      <em>
      <?= substr(strip_tags(preg_replace('/(<br\s?\/?>)/i', ' ', mmrpg_formatting_decode($temp_thread_body))), 0, 300).'&hellip;' ?>
      <a class="link_inline" href="<?= $this_thread_url ?>">Read More</a>
      </em>
    </div>
    <? if((COMMUNITY_VIEW_MODERATOR || $this_userinfo['user_id'] == $this_thread_info['user_id']) && $this_thread_info['category_id'] != 0): ?>
      <? if($this_thread_info['thread_target'] == 0): ?>
      <div class="published" style="position: absolute; bottom: 10px; right: 10px;">
        <?/*<strong><?= $temp_thread_author ?></strong> on <strong><?= $temp_thread_date ?></strong>*/?>
          <span class="options">[ <a class="edit" rel="noindex,nofollow" href="<?= $_GET['this_current_url'].'action=edit&amp;thread_id='.$this_thread_info['thread_id'].'#discussion-form' ?>">edit</a> ]</span>
      </div>
      <? endif; ?>
    <? endif; ?>
    <div class="viewed" style="position: absolute; bottom: 12px; left: 14px; right: 14px; font-size: 10px; line-height: 13px; color: #565656; text-shadow: 0 0 0 transparent; border-top: 1px solid #252424; padding-top: 6px; width: 100%; ">
      <?
      // If this is a personal message, only display the time
      if ($this_category_info['category_id'] == 0){ echo 'Sent by '.$temp_thread_author.' to '.$temp_thread_target.' on '.$temp_thread_date; }
      // Otherwise display extended details about the post
      else { echo $temp_thread_name.'<br /> Posted by '.$temp_thread_author.' on '.$temp_thread_date.'<br /> '.($temp_thread_views == 1 ? 'Viewed 1 Time' : 'Viewed '.$temp_thread_views.' Times'); }
      ?>
    </div>
  </div>
<? } ?>

<div class="posts_body">

  <?

  // Define whether or not we should show the comment section
  $show_comment_section = ($this_userid != MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked']) && $community_battle_points >= MMRPG_SETTINGS_POST_MINPOINTS) || $this_category_info['category_token'] == 'personal' ? true : false;
  $this_comment_page_links = '';
  $this_comment_page_header = '';
  $this_comment_page_footer = '';

  // Loop through all the posts and display them in reverse order
  if (!empty($this_posts_count)){

    // Generate the comment page links for this thread, if there are any
    ob_start();
    if ($this_pages_count > 1){
      ?>
      <div class="thread_posts_pages">
        <strong>Page</strong> :
        <?
        // Loop through the pages and print them on the page
        for ($page_num = 1; $page_num <= $this_pages_count; $page_num++){
          $temp_active = $page_num == $this_page_current ? true : false;
          $temp_class = 'link '.($temp_active ? 'active ' : '');
          $temp_href = $this_thread_url.($page_num > 1 ? $page_num.'/' : '');
          echo '<a class="'.$temp_class.'" href="'.$temp_href.'">'.$page_num.'</a>'."\n";

        } ?>
      </div>
      <?
    }
    $this_comment_page_links = ob_get_clean();

    // Generate the comment page header for this thread, so we can reuse
    ob_start();
      if ($this_category_info['category_id'] != 0){
        ?>
        <div id="comment-listing" class="subheader thread_posts_count field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
          <h2 class="inner_subheader"><?= $this_posts_count == 1 ? '1 Comment' : $this_posts_count.' Comments' ?></h2>
          <?= $this_comment_page_links ?>
        </div>
        <?
      } else {
        ?>
        <div id="comment-listing" class="subheader thread_posts_count field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
          <h2 class="inner_subheader"><?= $this_posts_count == 1 ? '1 Reply' : $this_posts_count.' Replies' ?></h2>
          <?= $this_comment_page_links ?>
        </div>
        <?
      }
    $this_comment_page_header = ob_get_clean();

    // Generate the comment page header for this thread, so we can reuse
    ob_start();
      if (!empty($this_comment_page_links)){
        ?>
        <div id="comment-listing-2" class="subheader thread_posts_count field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
          <?= $this_comment_page_links ?>
        </div>
        <?
      }
    $this_comment_page_footer = ob_get_clean();

    // Print out the comment header here at the top of the page
    echo $this_comment_page_header."\n";

    // Loop through each of the posts and display their markup
    foreach ($this_posts_array AS $this_post_key => $this_post_info){

      // If the post key is out of range, do not show it
      if ($this_post_key < $this_page_minpost_key){ continue; }
      elseif ($this_post_key > $this_page_maxpost_key){ break; }

      // Collect markup for this post from the function
      $temp_markup = mmrpg_website_community_postblock($this_thread_info, $this_post_info, $this_category_info);
      echo $temp_markup."\n";

    }

  }

  // Print out the comment header again at the bottom of the page
  if (!$show_comment_section){ echo $this_comment_page_footer."\n"; }

  ?>

  <? if($show_comment_section): ?>
    <div id="comment-form" class="subheader thread_posts_count field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <h2 class="inner_subheader"><?= !empty($_REQUEST['post_id']) ? 'Edit' : 'Post' ?> Comment</h2>
      <?= !empty($_REQUEST['post_id']) ? '<a class="link" style="float: right; color: rgb(146, 146, 146); " href="'.$_GET['this_current_url'].'#comment-listing">Cancel</a>' : $this_comment_page_links ?>
    </div>
    <div class="subbody thread_posts_form post_subbody">
      <form class="form" action="<?= $_GET['this_current_url'].(!empty($_REQUEST['post_id']) ? 'post_id='.$_REQUEST['post_id'] : '').(!empty($_REQUEST['post_id']) ? '#post-'.$_REQUEST['post_id'] : '#comment-listing') ?>" method="post">
        <? if (defined('COMMENT_POST_SUCCESSFUL') && COMMENT_POST_SUCCESSFUL === true): ?>
          <p class="text" style="color: #65C054; margin: 0;">(!) Thank you, your comment has been <?= !empty($_REQUEST['post_id']) ? 'edited' : 'posted' ?>!<br />Would you like to <a style="color: #65C054;" href="<?= $_GET['this_current_url'] ?>">reload the page</a>?</p>
          <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%; margin: 0;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
        <? elseif (defined('COMMENT_POST_SUCCESSFUL') && COMMENT_POST_SUCCESSFUL === false): ?>
          <p class="text" style="color: #E43131; margin: 0;">(!) Your comment could not be posted. Please review and correct the errors below.</p>
          <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%; margin: 0;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
        <? endif;?>
        <? if (!defined('COMMENT_POST_SUCCESSFUL') || (defined('COMMENT_POST_SUCCESSFUL') && COMMENT_POST_SUCCESSFUL === false)): ?>
          <?
          // Define and display the avatar variables
          $temp_avatar_guest = $this_userid == MMRPG_SETTINGS_GUEST_ID ? true : false;
          $temp_avatar_name = (!empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name']);
          $temp_avatar_title = '#'.$this_userid.' : '.$temp_avatar_name;

          // Define the avatar class and path variables
          $temp_avatar_path = !$temp_avatar_guest ? (!empty($this_userinfo['user_image_path']) ? $this_userinfo['user_image_path'] : 'robots/mega-man/40') : 'robots/robot/40';  //!empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
          $temp_post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;
          $temp_post_body = isset($_POST['post_body']) ? htmlentities($_POST['post_body'], ENT_QUOTES, 'UTF-8', true) : '';
          $temp_avatar_frame = isset($_REQUEST['post_frame']) ? $_REQUEST['post_frame'] : '00';
          if (!empty($temp_post_id)){
            $temp_post_info = $DB->get_array("SELECT mmrpg_posts.*, mmrpg_users.user_image_path FROM mmrpg_posts LEFT JOIN mmrpg_users on mmrpg_users.user_id = mmrpg_posts.user_id WHERE post_id = {$temp_post_id} ".(!COMMUNITY_VIEW_MODERATOR ? " AND mmrpg_posts.user_id = {$this_userid}" : ''));
            //die('$temp_post_info = <pre>'.print_r($temp_post_info, true).'</pre>');
            $temp_post_body = !empty($temp_post_info['post_body']) ? htmlentities($temp_post_info['post_body'], ENT_QUOTES, 'UTF-8', true) : '';
            $temp_avatar_path = !empty($temp_post_info['user_image_path']) ? $temp_post_info['user_image_path'] : $temp_avatar_path;
            $temp_avatar_frame = !empty($temp_post_info['post_frame']) ? $temp_post_info['post_frame'] : $temp_avatar_frame;
          }
          list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
          $temp_avatar_size = $temp_avatar_size * 2;
          $temp_avatar_frames_count = $temp_avatar_kind == 'players' ? 6 : 10;
          $temp_avatar_frames = array();
          for ($i = 0; $i < $temp_avatar_frames_count; $i++){ $temp_avatar_frames[] = str_pad($i, 2, '0', STR_PAD_LEFT); }
          $temp_avatar_frames = implode(',', $temp_avatar_frames);
          $temp_avatar_class = 'avatar avatar_80x80 float float_left avatar_selector ';
          $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
          $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_right_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;

          // Collect the post target if applicable
          $temp_post_target = 0;
          if ($this_thread_info['user_id'] != $this_userinfo['user_id']){ $temp_post_target = $this_thread_info['user_id']; }
          elseif (!empty($this_thread_info['thread_target']) && $this_thread_info['thread_target'] != $this_userinfo['user_id']){ $temp_post_target = $this_thread_info['thread_target']; }

          ?>
          <input type="hidden" class="hidden" name="formaction" value="post" />
          <input type="hidden" class="hidden" name="category_id" value="<?= $this_category_info['category_id'] ?>" />
          <input type="hidden" class="hidden" name="thread_id" value="<?= $this_thread_info['thread_id'] ?>" />
          <input type="hidden" class="hidden" name="user_id" value="<?= COMMUNITY_VIEW_MODERATOR && !empty($temp_post_info['user_id']) ? $temp_post_info['user_id'] : $this_userinfo['user_id'] ?>" />
          <input type="hidden" class="hidden" name="post_id" value="<?= $temp_post_id ?>" />
          <input type="hidden" class="hidden" name="user_ip" value="<?= !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0' ?>" />
          <input type="hidden" class="hidden" name="post_frame" value="<?= $temp_avatar_frame ?>" />
          <input type="hidden" class="hidden" name="post_time" value="<?= time() ?>" />
          <?= $this_category_info['category_id'] == 0 ? '<input type="hidden" class="hidden" name="post_target" value="'.$temp_post_target.'" />' : '' ?>
          <div class="field field_post_info" style="overflow: hidden; font-size: 11px;">
            <label class="label" style="float: left;"><?= $temp_avatar_guest ? 'Posting as' : 'Logged in as' ?> <strong><?= $temp_avatar_name ?></strong> :</label>
          </div>
          <div class="field field_post_body">
            <div class="<?= $temp_avatar_class ?>" style="">
              <div class="<?= $temp_sprite_class ?>" data-frames="<?=$temp_avatar_frames?>" style="background-image: url(<?= $temp_sprite_path ?>); "><?= $temp_avatar_title ?></div>
              <a class="back">&#9668;</a>
              <a class="next">&#9658;</a>
            </div>
            <? /*<textarea class="textarea" name="post_body" rows="10"><?= str_replace("\n", '\\n', $temp_post_body) ?></textarea>*/ ?>
            <textarea class="textarea" name="post_body" rows="10"><?= $temp_post_body ?></textarea>
          </div>
          <div class="field field_post_info" style="clear: left; overflow: hidden; font-size: 11px;">
            <?= mmrpg_formatting_help() ?>
          </div>
          <?
          // Define the current maxlength based on board points
          $temp_maxlength = MMRPG_SETTINGS_DISCUSSION_MAXLENGTH;
          if (!empty($this_boardinfo['board_points']) && ceil($this_boardinfo['board_points'] / 1000) > MMRPG_SETTINGS_DISCUSSION_MAXLENGTH){ $temp_maxlength = ceil($this_boardinfo['board_points'] / 1000); }
          ?>
          <div class="buttons buttons_active" data-submit="<?= !empty($_REQUEST['post_id']) ? 'Edit' : 'Post' ?> Comment">
            <label class="counter"><span class="current">0</span> / <span class="maximum"><?= $temp_maxlength ?></span> Characters</label>
          </div>
        <? endif; ?>
      </form>
    </div>
  <? elseif($this_userid != MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked']) && $community_battle_points < MMRPG_SETTINGS_POST_MINPOINTS && $this_category_info['category_token'] != 'personal'): ?>
    <h2 id="comment-form" class="subheader thread_posts_count" style="opacity: 0.5; filter: alpha(opacity = 50);">- you need at least <?= number_format(MMRPG_SETTINGS_POST_MINPOINTS, 0, '.', ',') ?> battle points to post a comment -</h2>
  <? elseif($this_userid == MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked'])): ?>
    <h2 id="comment-form" class="subheader thread_posts_count" style="opacity: 0.5; filter: alpha(opacity = 50);">- <a href="file/load/return=<?= htmlentities($this_current_uri.(!empty($_REQUEST['post_id']) ? 'post_id='.$_REQUEST['post_id'] : '').(!empty($_REQUEST['post_id']) ? '#comment-listing' : '')) ?>" rel="noindex,nofollow" style="color: #FFFFFF;">login to comment</a> -</h2>
  <? else: ?>
    <h2 id="comment-form" class="subheader thread_posts_count" style="opacity: 0.5; filter: alpha(opacity = 50);">- comments disabled -</h2>
  <? endif; ?>

</div>
<?

// Add this thread to the community session tracker array
if (!in_array($thread_session_token, $_SESSION['COMMUNITY']['threads_viewed'])){
  $_SESSION['COMMUNITY']['threads_viewed'][] = $thread_session_token;
}

?>