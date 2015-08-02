<?
/*
 * COMMUNITY INDEX VIEW
 */

// Update the SEO variables for this page
//$this_seo_title = $this_category_info['category_name'].' | '.$this_seo_title;

// Update the MARKUP variables for this page
//$this_markup_header = $this_thread_info['thread_name']; //.' | '.$this_markup_header;

// Collect the current user's info from the database
//$this_userinfo = $DB->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");



// Collect all the threads for this category from the database
$index_threads_query = "SELECT threads.*, users.*, users2.*, users3.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
  LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
  LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_name_clean AS mod_user_name_clean, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
  LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_name_clean AS target_user_name_clean, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
  LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
  LEFT JOIN (
  SELECT posts.thread_id, count(1) AS post_count
  FROM mmrpg_posts AS posts WHERE posts.post_deleted = 0
  GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
  WHERE threads.thread_published = 1
  ORDER BY threads.thread_sticky DESC, threads.thread_date DESC";
$index_threads_array = $DB->get_array_list($index_threads_query);
$index_threads_count = !empty($index_threads_array) ? count($index_threads_array) : 0;
if (empty($index_threads_array)){ $index_threads_array = array(); }

// Loop through the different categories and collect their threads one by one
$this_category_key = 0;
foreach ($this_categories_index AS $this_category_id => $this_category_info){

  // If this is the personal message center, or another special category, do not display on index
  if ($this_category_info['category_token'] == 'personal' || $this_category_info['category_token'] == 'chat' || $this_category_info['category_token'] == 'search'){ continue; }

  // Collect the thread array for this category
  $this_threads_array = array();
  foreach ($index_threads_array AS $thread){ if ($thread['category_id'] == $this_category_info['category_id']){ $this_threads_array[] = $thread; } }
  $this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;
  $this_threads_count_more = $this_threads_count - MMRPG_SETTINGS_THREADS_RECENT;

  // If this is the news category, ensure the threads are arranged by date only
  if ($this_category_info['category_token'] == 'news'){
    function temp_community_news_sort($thread1, $thread2){
      if ($thread1['thread_date'] > $thread2['thread_date']){ return -1; }
      elseif ($thread1['thread_date'] < $thread2['thread_date']){ return 1; }
      else { return 0; }
    }
    usort($this_threads_array, 'temp_community_news_sort');
  }

  // Define the extra links array for the header
  $temp_header_links = array();
  // If there are more threads in this category to display, show the more link
  if($this_threads_count_more > 0){
    $temp_header_links[] = array(
      'href' => 'community/'.$this_category_info['category_token'].'/',
      'title' => 'View '.($this_threads_count_more == '1' ? '1 More '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : $this_threads_count_more.' More '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages')),
      'class' => 'field_type field_type_none'
      );
  }
  // If this user has the necessary permissions, show the new thread link
  if($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userinfo['role_level'] >= $this_category_info['category_level'] && $community_battle_points >= 10000){
    $temp_header_links[] = array(
      'href' => 'community/'.$this_category_info['category_token'].'/0/new/',
      'title' => 'Create New '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message'),
      'class' => 'field_type field_type_none'
      );
  }
  // If there are new threads in this category, show the new/recent link
  $this_threads_count_new = !empty($_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']]) ? $_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']] : 0;
  if ($this_threads_count_new > 0){
    $temp_header_links[] = array(
      'href' => 'community/'.$this_category_info['category_token'].'/new/',
      'title' => 'View '.($this_threads_count_new == '1' ? '1 Updated Thread' : $this_threads_count_new.' Updated Threads'),
      'class' => 'field_type field_type_electric'
      );
  }
  // Reverse them for display purposes
  $temp_header_links = array_reverse($temp_header_links);
  // Loop through and generate the appropriate markup to display
  if (!empty($temp_header_links)){
    foreach ($temp_header_links AS $key => $info){
      $temp_header_links[$key] = '<a class="float_link float_link2 '.(!empty($info['class']) ? $info['class'] : '').'" style="right: '.(10 + (135 * $key)).'px;" href="'.$info['href'].'">'.$info['title'].' &raquo;</a>';
    }
  }

  ?>
  <h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="clear: both; <?= $this_category_key > 0 ? 'margin-top: 6px; ' : '' ?>">
    <a class="link" href="<?= 'community/'.$this_category_info['category_token'].'/' ?>" style="display: inline;"><?= $this_category_info['category_name'] ?>  <span class="count">( <?= ($this_threads_count > MMRPG_SETTINGS_THREADS_RECENT  ? MMRPG_SETTINGS_THREADS_RECENT.' of ' : '').($this_threads_count == '1' ? '1 '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : $this_threads_count.' '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages'))  ?> )</span></a>
    <?= !empty($temp_header_links) ? implode("\n", $temp_header_links) : '' ?>
  </h2>
  <div style="overflow: hidden; margin-bottom: 25px;">
  <?

  // Define the current date group
  $this_date_group = '';

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

  // Close the container tag
  ?>
  </div>
  <? if(false){ ?>
    <div class="subbody" style="margin-bottom: 6px; background-color: transparent; padding-right: 0;">
      <?/*<div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_0<?= mt_rand(0, 2) ?>" style="background-image: url(images/robots/<?= MMRPG_SETTINGS_CURRENT_FIELDMECHA ?>/sprite_left_80x80.png);">Met</div></div>*/?>
      <?/*<p class="text"><?= $this_category_info['category_description'] ?></p>*/?>
      <?
      // Add a new thread option to the end of the list if allowed
      if($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userinfo['role_level'] >= $this_category_info['category_level'] && $community_battle_points > 10000){
        ?>
        <div class="subheader thread_name" style="float: right; margin: 0; overflow: hidden; text-align: center; border: 1px solid rgba(0, 0, 0, 0.30); ">
          <a class="link" href="community/<?= $this_category_info['category_token'] ?>/0/new/" style="margin-top: 0;">Create New <?= $this_category_info['category_id'] != 0 ? 'Discussion' : 'Message' ?> &raquo;</a>
        </div>
        <?
      }
      ?>
    </div>
  <? } ?>
  <?
  $this_category_key++;
}

?>