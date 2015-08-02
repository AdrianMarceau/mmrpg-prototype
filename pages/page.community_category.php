<?
/*
 * COMMUNITY CATEGORY VIEW
 */

// Update the SEO variables for this page
$this_seo_title = $this_category_info['category_name'].' | '.$this_seo_title;
$this_seo_description = strip_tags($this_category_info['category_description']);

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Community Forums | '.$this_category_info['category_name'];
$this_graph_data['description'] = strip_tags($this_category_info['category_description']);
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE;
//$this_graph_data['type'] = 'website';

// Update the MARKUP variables for this page
//$this_markup_header = $this_thread_info['thread_name']; //.' | '.$this_markup_header;

// Collect the current user's info from the database
//$this_userinfo = $DB->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");

// If this is a SEARCH request, we run a special query
if ($this_category_info['category_token'] == 'search'){

  // Require the search category page, we should have separated this a while ago
  require('page.community_category_search.php');

}
// Else we run this category query normally
else {

  // Define the ORDER BY string based on category key
  if ($this_category_info['category_token'] != 'news'){ $temp_order_by = 'threads.thread_sticky DESC, threads.thread_mod_date DESC, threads.thread_date DESC'; }
  else { $temp_order_by = 'threads.thread_sticky DESC, threads.thread_date DESC'; }

  // Collect all the threads for this category from the database
  $this_threads_query = "SELECT threads.*, users.*, users2.*, users3.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
    LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
      LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_name_clean AS mod_user_name_clean, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
      LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_name_clean AS target_user_name_clean, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
    LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
    LEFT JOIN (
    SELECT posts.thread_id, count(1) AS post_count
    FROM mmrpg_posts AS posts
    GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
    WHERE threads.category_id = {$this_category_info['category_id']} AND threads.thread_published = 1 AND (threads.thread_target = 0 OR threads.thread_target = {$this_userinfo['user_id']} OR threads.user_id = {$this_userinfo['user_id']})
    ORDER BY {$temp_order_by}";
  $this_threads_array = $DB->get_array_list($this_threads_query);
  $this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;
  //die('<pre>'.print_r($this_threads_array, true).'</pre>');

}

?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
  <a class="link" style="display: inline;" href="<?= str_replace($this_category_info['category_token'].'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="raquo">&nbsp;&raquo;&nbsp;</span>
  <a class="link" style="display: inline;" href="<?= $_GET['this_current_url'] ?>"><?= $this_category_info['category_name'] ?></a>
  <? if(!in_array($this_category_info['category_token'], array('chat', 'search'))): ?>
    <span style="float: right;">
      <?= $this_threads_count == '1' ? '1 '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : $this_threads_count.' '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages')  ?>
      <?
        // Add the new threads option if there are new threads to view
        $this_threads_count_new = !empty($_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']]) ? $_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']] : 0;
        if ($this_threads_count_new > 0){
          ?>
          <span class="pipe">&nbsp;|&nbsp;</span>
          <a class="link" style="display: inline;" href="community/<?= $this_category_info['category_token'] ?>/new/" style="margin-top: 0;"><?= $this_threads_count_new == 1 ? 'View 1 Updated Thread' : 'View '.$this_threads_count_new.' Updated Threads' ?> &raquo;</a>
          <?
        }
        // Add a new thread option to the end of the list if allowed
        if($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userinfo['role_level'] >= $this_category_info['category_level'] && $community_battle_points >= 10000){
          ?>
          <span class="pipe">&nbsp;|&nbsp;</span>
          <a class="link" style="display: inline;" href="community/<?= $this_category_info['category_token'] ?>/0/new/" style="margin-top: 0;">Create New Discussion &raquo;</a>
          <?
        }
        ?>
    </span>
  <? endif; ?>
</h2>
<?
// Only display the category body if not personal
if ($this_category_info['category_id'] != 0){
  // Require the leaderboard data file
  require_once('data/leaderboard.php');
  // Collect all the active sessions for this page
  $temp_viewing_category = mmrpg_website_sessions_active('community/'.$this_category_info['category_token'].'/', 3, true);
  $temp_viewing_userids = array();
  foreach ($temp_viewing_category AS $session){ $temp_viewing_userids[] = $session['user_id']; }
  ?>
  <div class="subbody">
    <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_0<?= mt_rand(0, 2) ?>" style="background-image: url(images/robots/<?= MMRPG_SETTINGS_CURRENT_FIELDMECHA ?>/sprite_left_80x80.png);"><?= MMRPG_SETTINGS_CURRENT_FIELDMECHA ?></div></div>
    <p class="text"><?= $this_category_info['category_description'] ?></p>
    <?
    // If this is the SEARCH discussion category, print a form
    if ($this_category_info['category_token'] == 'search'){
      // Define the display text for the online player section
      $this_online_label = 'Searching';
      $this_online_tooltip = 'A ranked leaderboard profile list of any online players who recently used the search page to navigate the community.';
      ?>

      <form class="search_form" method="get" style="overflow: hidden; clear: both; padding-top: 4px;">

        <div class="section">
          <h3 class="subheader field_type_empty">Search by Keyword</h3>
          <div class="field field_text">
            <input class="textinput" type="text" name="text" value="<?= !empty($temp_filter_data['text']) ? $temp_filter_data['text'] : '' ?>" style="width: 98%; " />
          </div>
          <div class="field field_limit">
            <div class="option option_all">
              <input class="radio" type="radio" id="option_limit_all" name="text_limit" value="all" <?= empty($temp_filter_data['text_limit']) || $temp_filter_data['text_limit'] == 'all' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_limit_all">Search entire post</label>
            </div>
            <div class="option option_name">
              <input class="radio" type="radio" id="option_limit_name" name="text_limit" value="name" <?= !empty($temp_filter_data['text_limit']) && $temp_filter_data['text_limit'] == 'name' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_limit_name">Search titles only</label>
            </div>
            <div class="option option_body">
              <input class="radio" type="radio" id="option_limit_body" name="text_limit" value="body" <?= !empty($temp_filter_data['text_limit']) && $temp_filter_data['text_limit'] == 'body' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_limit_body">Search content only</label>
            </div>
          </div>
        </div>

        <div class="section">
          <h3 class="subheader field_type_empty">Search by Username</h3>
          <div class="field field_player">
            <input class="textinput" type="text" name="player" value="<?= !empty($temp_filter_data['player']) ? $temp_filter_data['player'] : '' ?>" style="width: 98%; " />
          </div>
          <div class="field field_strict">
            <div class="option option_true">
              <input class="checkbox" type="checkbox" id="option_strict_true" name="player_strict" value="true" <?= !empty($temp_filter_data['player_strict']) ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_strict_true">Match exact username</label>
            </div>
          </div>
        </div>

        <div class="section" style="clear: left; ">
          <h3 class="subheader field_type_empty">Filter by Category</h3>
          <div class="field field_category">
            <select class="select" name="category" size="9">
              <option value="" <?= empty($temp_filter_data['category']) ? 'selected="selected"' : '' ?>>Any Category</option>
              <option value="">----------</option>
              <?
              // Loop through and display categories as options
              if (!empty($this_categories_index)){
                foreach ($this_categories_index AS $token => $info){
                  if (in_array($token, array('personal', 'chat', 'search'))){ continue; }
                  $temp_selected = !empty($temp_filter_data['category']) && $temp_filter_data['category'] == $token  ? ' selected="selected"' : '';
                  echo '<option value="'.$token.'"'.$temp_selected.'>'.$info['category_name'].'</option>';
                }
              }
              ?>
            </select>
          </div>
        </div>

        <div class="section">
          <h3 class="subheader field_type_empty">Search Options</h3>
          <div class="field field_display">
            <div class="option option_threads">
              <input class="radio" type="radio" id="option_display_threads" name="display" value="threads" <?= empty($temp_filter_data['display']) || $temp_filter_data['display'] == 'threads' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_display_threads">Display results as threads</label>
            </div>
            <div class="option option_posts">
              <input class="radio" type="radio" id="option_display_posts" name="display" value="posts" <?= !empty($temp_filter_data['display']) && $temp_filter_data['display'] == 'posts' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_display_posts">Display results as posts</label>
            </div>
          </div>
          <hr class="divider" />
          <div class="field field_sort">
            <div class="option option_desc">
              <input class="radio" type="radio" id="option_sort_desc" name="sort" value="desc" <?= empty($temp_filter_data['sort']) || $temp_filter_data['sort'] == 'desc' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_sort_desc">Sort results by newest first</label>
            </div>
            <div class="option option_asc">
              <input class="radio" type="radio" id="option_sort_asc" name="sort" value="asc" <?= !empty($temp_filter_data['sort']) && $temp_filter_data['sort'] == 'asc' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_sort_asc">Sort results by oldest first</label>
            </div>
          </div>
          <hr class="divider" />
          <div class="field field_limit">
            <div class="option option_threads">
              <input class="radio" type="radio" id="option_limit_all" name="limit" value="all" <?= empty($temp_filter_data['limit']) || $temp_filter_data['limit'] == 'all' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_limit_all">Do not limit my search results</label>
            </div>
            <div class="option option_threads">
              <input class="radio" type="radio" id="option_limit_threads" name="limit" value="threads" <?= !empty($temp_filter_data['limit']) && $temp_filter_data['limit'] == 'threads' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_limit_threads">Limit search to threads only</label>
            </div>
            <div class="option option_posts">
              <input class="radio" type="radio" id="option_limit_posts" name="limit" value="posts" <?= !empty($temp_filter_data['limit']) && $temp_filter_data['limit'] == 'posts' ? 'checked="checked"' : '' ?> />
              <label class="label" for="option_limit_posts">Limit search to posts only</label>
            </div>
            <?/*
            <hr class="divider" />
            <div class="field field_count">
              <select class="select" name="count">
                <option value="50" <?= empty($temp_filter_data['count']) || $temp_filter_data['count'] == 50 ? 'selected="selected"' : '' ?>>Show 50 Results</option>
                <option value="100" <?= !empty($temp_filter_data['count']) && $temp_filter_data['count'] == 100 ? 'selected="selected"' : '' ?>>Show 100 Results</option>
                <option value="250" <?= !empty($temp_filter_data['count']) && $temp_filter_data['count'] == 250 ? 'selected="selected"' : '' ?>>Show 250 Results</option>
                <option value="500" <?= !empty($temp_filter_data['count']) && $temp_filter_data['count'] == 500 ? 'selected="selected"' : '' ?>>Show 500 Results</option>
                <option value="all" <?= !empty($temp_filter_data['count']) && $temp_filter_data['count'] == 'all' ? 'selected="selected"' : '' ?>>Show All Results</option>
              </select>
            </div>
            */?>
          </div>
        </div>

        <?
        // Collect the current page num for the search results, and define min/max keys
        $search_page_num =  !empty($_REQUEST['pg']) && is_numeric($_REQUEST['pg']) ? $_REQUEST['pg'] : 1;
        $search_page_result_count = $temp_filter_data['display'] == 'threads' ? $this_threads_count : $this_posts_count;
        $search_page_link_count = $search_page_result_count > MMRPG_SETTINGS_SEARCH_PERPAGE ? ceil($search_page_result_count / MMRPG_SETTINGS_SEARCH_PERPAGE) : 1;
        $search_page_result_key_start = ($search_page_num * MMRPG_SETTINGS_SEARCH_PERPAGE) - MMRPG_SETTINGS_SEARCH_PERPAGE;
        $search_page_result_key_break = $search_page_result_key_start + MMRPG_SETTINGS_SEARCH_PERPAGE - 1;
        ?>

        <div class="buttons" style="float: none; clear: left; text-align: center; margin-top: 20px;">
          <?/*<input class="hidden" type="hidden" name="pg" value="<?= $search_page_num ?>" />*/?>
          <input class="button submit" type="submit" value="Search" style="font-size: 120%;" />
        </div>

        <? if($this_category_info['category_token'] == 'search' && !empty($search_page_result_count)): ?>
          <div class="results" style="font-size: 120%; ">
            <div class="count"><?
              echo 'Found ';
              if ($temp_filter_data['limit'] == 'threads'){ echo $thread_search_count.' Threads'; }
              elseif ($temp_filter_data['limit'] == 'posts'){ echo $post_search_count.' Posts'; }
              else {
                if (!empty($thread_search_count)){ echo $thread_search_count.' Threads'; }
                if (!empty($thread_search_count) && !empty($post_search_count)){ echo ', '; }
                if (!empty($post_search_count)){ echo $post_search_count.' Posts'; }
              }
              echo ' <span class="total">'.$search_page_result_count.' Results Total</span>';
            ?></div>
            <? if($search_page_link_count > 1): ?>
              <div class="pages">
                <span class="label">Pages</span>
                <?
                // Gather all the other fields into a single query string
                $temp_query_string = array();
                if (isset($temp_filter_data['text'])){ $temp_query_string[] = 'text='.$temp_filter_data['text']; }
                if (isset($temp_filter_data['text_limit'])){ $temp_query_string[] = 'text_limit='.$temp_filter_data['text_limit']; }
                if (isset($temp_filter_data['player'])){ $temp_query_string[] = 'player='.$temp_filter_data['player']; }
                if (isset($temp_filter_data['category'])){ $temp_query_string[] = 'category='.$temp_filter_data['category']; }
                if (isset($temp_filter_data['display'])){ $temp_query_string[] = 'display='.$temp_filter_data['display']; }
                if (isset($temp_filter_data['sort'])){ $temp_query_string[] = 'sort='.$temp_filter_data['sort']; }
                if (isset($temp_filter_data['limit'])){ $temp_query_string[] = 'limit='.$temp_filter_data['limit']; }
                $temp_query_string = implode('&amp;', $temp_query_string);
                // Loop through and print links for page nums
                for ($num = 1; $num <= $search_page_link_count; $num++){
                  $class = 'link'.($num == $search_page_num ? ' active' : '');
                  $href = $_GET['this_current_url'].'?'.$temp_query_string.'&amp;pg='.$num;
                  echo '<a class="'.$class.'" href="'.$href.'">'.$num.'</a>';
                }
                ?>
              </div>
            <? endif; ?>
          </div>
        <? endif; ?>

      </form>

      <?
    }
    // Otherwise, if this just a regular discussion category, print normally
    else {
      // Define the display text for the online player section
      $this_online_label = 'Browsing';
      $this_online_tooltip = 'A ranked leaderboard profile list of any online players who recently viewed this community category or any of its threads.';
    }
    ?>
  </div>
  <?

  // Print out any online viewers if they exist
  $temp_markup = !empty($temp_viewing_userids) ? mmrpg_website_print_online($this_leaderboard_online_players, $temp_viewing_userids) : '';
  if (!empty($temp_markup)){
    ?>
    <div class="subbody online_players">
      <p class="text desc"><?= $this_online_tooltip ?></p>
      <div class="text event players"><?= $temp_markup ?></div>
      <strong class="text label"><?= (count($temp_viewing_userids) == 1 ? '1 Player' : count($temp_viewing_userids).' Players').' '.$this_online_label ?></strong>
    </div>
    <?
  }

}

/*
if (true){
  ?>
  <div class="field_type field_type_empty" style="padding: 10px; font-size: 12px; font-family: Courier New; color: white;">
    <?=
    '<hr /> '.
    '$post_search_array = '.count($post_search_array).'<br /><small>('.implode(',', array_keys($post_search_array)).')</small><br /> '.
    '$thread_search_array = '.count($thread_search_array).'<br /><small>('.implode(',', array_keys($thread_search_array)).')</small><br /> '.
    '<hr /> '.
    '$post_index_array_required = '.count($post_index_array_required).'<br /><small>('.implode(',', $post_index_array_required).')</small><br /> '.
    '$thread_index_array_required = '.count($thread_index_array_required).'<br /><small>('.implode(',', $thread_index_array_required).')</small><br /> '.
    '<hr /> '.
    '$post_index_array = '.count($post_index_array).'<br /><small>('.implode(',', array_keys($post_index_array)).')</small><br /> '.
    '$thread_index_array = '.count($thread_index_array).'<br /><small>('.implode(',', array_keys($thread_index_array)).')</small><br /> '.
    '<hr /> '.
    '$this_user_ids_array = '.count($this_user_ids_array).'<br /><small>('.implode(',', $this_user_ids_array).')</small><br /> '.
    '<hr /> '.
    '$this_posts_array = '.count($this_posts_array).'<br /><small>('.implode(',', array_keys($this_posts_array)).')</small><br /> '.
    '$this_threads_array = '.count($this_threads_array).'<br /><small>('.implode(',', array_keys($this_threads_array)).')</small><br /> '.
    '<hr /> '
    ?>
    <?= false && !empty($_REQUEST) ? '<pre>$_REQUEST = '.print_r($_REQUEST, true).'</pre>' : '' ?>
  </div>
  <?
}
*/


// Define the current date group
$this_date_group = '';

// Define the temporary timeout variables
$this_time = time();
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

// Collect the user post and thread count index plus leaderboard points for display
$temp_id_includes = !empty($this_user_ids_array) ? 'AND mmrpg_users.user_id IN ('.implode(', ', $this_user_ids_array).')' : '';
if (!empty($temp_id_includes)){
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
    WHERE mmrpg_leaderboard.board_points > 0 '.$temp_id_includes, 'user_id');
} else {
  $this_user_countindex = array();
}

// If we're not on the search page, display threads normally
if ($this_category_info['category_token'] != 'search'){

  // Loop through the thread array and display its contents
  if (!empty($this_threads_array)){
    foreach ($this_threads_array AS $this_thread_key => $this_thread_info){

      // Collect markup for this thread from the function
      $temp_markup = mmrpg_website_community_thread_linkblock($this_thread_info, $this_category_info);
      echo $temp_markup."\n";

    }
  } else {
    ?>
    <div class="subbody">
      <p class="text">- there are no <?= $this_category_info['category_id'] != 0 ? 'threads' : 'messages' ?> to display -</p>
    </div>
    <?
  }

}
// Else if the display filter is set to display threads
elseif ($this_category_info['category_token'] == 'search' && $temp_filter_data['display'] == 'threads'){


  // Loop through the thread array and display its contents
  if (!empty($this_threads_array)){
    foreach ($this_threads_array AS $this_thread_key => $this_thread_info){

      // Check the key to see if we should display this result
      if ($this_thread_key < $search_page_result_key_start){ continue; }
      elseif ($this_thread_key >= $search_page_result_key_break){ break; }


      // Collect markup for this thread from the function
      $temp_markup = mmrpg_website_community_thread_linkblock($this_thread_info, $this_category_info);
      echo $temp_markup."\n";

    }
  } else {
    ?>
    <div class="subbody">
      <? if (!empty($temp_filter_data['limit']) && $temp_filter_data['limit'] == 'posts'): ?>
        <p class="text">- there are no posts to display -</p>
      <? elseif (!empty($temp_filter_data['limit']) && $temp_filter_data['limit'] == 'threads'): ?>
        <p class="text">- there are no threads to display -</p>
      <? else : ?>
        <p class="text">- there are no results to display -</p>
      <? endif; ?>
    </div>
    <?
  }

}
// Otherwise, if the display filter is set to display posts
elseif ($this_category_info['category_token'] == 'search' && $temp_filter_data['display'] == 'posts'){

  // Loop through the post array and display its contents
  if (!empty($this_posts_array)){
    foreach ($this_posts_array AS $this_post_key => $this_post_info){

      // Check the key to see if we should display this result
      if ($this_post_key < $search_page_result_key_start){ continue; }
      elseif ($this_post_key >= $search_page_result_key_break){ break; }

      // Collect markup for this post from the function
      $temp_thread_info = $thread_index_array[$this_post_info['thread_id']];
      $temp_markup = mmrpg_website_community_postblock($temp_thread_info, $this_post_info, $this_category_info);
      echo $temp_markup."\n";

    }
  } else {
    ?>
    <div class="subbody">
      <? if (!empty($temp_filter_data['limit']) && $temp_filter_data['limit'] == 'posts'): ?>
        <p class="text">- there are no posts to display -</p>
      <? elseif (!empty($temp_filter_data['limit']) && $temp_filter_data['limit'] == 'threads'): ?>
        <p class="text">- there are no threads to display -</p>
      <? else : ?>
        <p class="text">- there are no results to display -</p>
      <? endif; ?>
    </div>
    <?
  }

}



?>