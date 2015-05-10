<?
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

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
  
// Define the ORDER BY string based on category key
if ($this_category_info['category_token'] != 'news'){ $temp_order_by = 'threads.thread_sticky DESC, threads.thread_mod_date DESC, threads.thread_date DESC'; }
else { $temp_order_by = 'threads.thread_sticky DESC, threads.thread_date DESC'; }

// Collect the current user's info from the database
//$this_userinfo = $DB->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");

// Collect all the threads for this category from the database
$this_threads_query = "SELECT threads.*, users.*, users2.*, users3.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
  LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
  LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
  LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
  LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
  LEFT JOIN (
  SELECT posts.thread_id, count(1) AS post_count
  FROM mmrpg_posts AS posts WHERE posts.post_deleted = 0
  GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
  WHERE threads.category_id = {$this_category_info['category_id']} AND threads.thread_published = 1 AND (threads.thread_target = 0 OR threads.thread_target = {$this_userinfo['user_id']} OR threads.user_id = {$this_userinfo['user_id']})
  ORDER BY threads.thread_locked ASC, {$temp_order_by}";
$this_threads_array = $DB->get_array_list($this_threads_query);
$this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;
//die('<pre>'.print_r($this_threads_array, true).'</pre>');

?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
  <a class="link" style="display: inline;" href="<?= str_replace($this_category_info['category_token'].'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
  <a class="link" style="display: inline;" href="<?= $_GET['this_current_url'] ?>"><?= $this_category_info['category_name'] ?></a>
  <span style="float: right; opacity: 0.25;"><?= $this_threads_count == '1' ? '1 '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : $this_threads_count.' '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages')  ?></span>
</h2>
<?
// Only display the category body if not personal
if ($this_category_info['category_id'] != 0){
  ?>
  <div class="subbody">
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_0<?= mt_rand(0, 2) ?>" style="background-image: url(images/robots/<?= MMRPG_SETTINGS_CURRENT_FIELDMECHA ?>/sprite_left_80x80.png);">Met</div></div>
  <p class="text"><?= $this_category_info['category_description'] ?></p>
  <div style="clear:both;">&nbsp;</div>
  <?
  // Add the new threads option if there are new threads to view
  $this_threads_count_new = !empty($_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']]) ? $_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']] : 0;
  if ($this_threads_count_new > 0){
    ?>
    <div class="subheader thread_name field_type field_type_electric" style="float: right; margin: 0 0 0 10px; overflow: hidden; text-align: center; border: 1px solid rgba(0, 0, 0, 0.30); ">
      <a class="link" href="community/<?= $this_category_info['category_token'] ?>/new/" style="margin-top: 0;"><?= $this_threads_count_new == 1 ? 'View 1 Updated Thread' : 'View '.$this_threads_count_new.' Updated Threads' ?> &raquo;</a>
    </div>
    <?
  }
  // Add a new thread option to the end of the list if allowed
  if($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userinfo['role_level'] >= $this_category_info['category_level'] && $community_battle_points >= 10000){
    ?>
    <div class="subheader thread_name" style="float: right; margin: 0 0 0 10px; overflow: hidden; text-align: center; border: 1px solid rgba(0, 0, 0, 0.30); ">
      <a class="link" href="community/<?= $this_category_info['category_token'] ?>/0/new/" style="margin-top: 0;">Create New Discussion &raquo;</a>
    </div>
    <?
  }
  ?>
  </div>
  <?
}

// Define the current date group
$this_date_group = '';

// Define the temporary timeout variables
$this_time = time();
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

// Loop through the thread array and display its contents
if (!empty($this_threads_array)){
  foreach ($this_threads_array AS $this_thread_key => $this_thread_info){
    
    // Define this thread's session tracker token
    $temp_session_token = $this_thread_info['thread_id'].'_';
    $temp_session_token .= !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];
    // Check if this thread has already been viewed this session
    $temp_session_viewed = in_array($temp_session_token, $_SESSION['COMMUNITY']['threads_viewed']) ? true : false;
    
    // Update the temp date group if necessary
    $temp_thread_date = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
    $temp_thread_mod_date = !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $temp_thread_date;
    $temp_date_group = date('Y-m', $temp_thread_mod_date);
    if (!empty($this_thread_info['thread_locked'])){ $temp_date_group = 'locked'; }
    elseif (!empty($this_thread_info['thread_sticky'])){ $temp_date_group = 'sticky'; }
    if ($temp_date_group != $this_date_group){
      $this_date_group = $temp_date_group;
      if ($temp_date_group == 'locked'){
        echo '<h3 id="date-'.$temp_date_group.'" data-group="'.$temp_date_group.'" class="subheader category_date_group" style="color: #464646;">Locked Threads</h3>';
      } elseif ($temp_date_group == 'sticky'){
        echo '<h3 id="date-'.$temp_date_group.'" data-group="'.$temp_date_group.'" class="subheader category_date_group">Sticky Threads</h3>';
      } else {
        echo '<h3 id="date-'.$temp_date_group.'" data-group="'.$temp_date_group.'" class="subheader category_date_group">'.date('F Y', $temp_thread_mod_date).'</h3>';
      }
      
    }
    
    // Define the temporary display variables
    $temp_category_id = $this_thread_info['category_id'];
    $temp_category_token = $this_thread_info['category_token'];
    $temp_thread_id = $this_thread_info['thread_id'];
    $temp_thread_token = $this_thread_info['thread_token'];
    $temp_thread_name = $this_thread_info['thread_name'];
    $temp_thread_author = !empty($this_thread_info['user_name_public']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];
    $temp_thread_author_colour = !empty($this_thread_info['user_colour_token']) ? $this_thread_info['user_colour_token'] : 'none';
    $temp_thread_date = date('F jS, Y', $temp_thread_date).' at '.date('g:ia', $temp_thread_date);
    $temp_thread_mod_user = !empty($this_thread_info['mod_user_name_public']) ? $this_thread_info['mod_user_name_public'] : $this_thread_info['mod_user_name'];
    $temp_thread_mod_date = !empty($this_thread_info['thread_mod_date']) && $this_thread_info['thread_mod_date'] != $this_thread_info['thread_date'] ? $this_thread_info['thread_mod_date'] : false;
    $temp_thread_mod_date = !empty($temp_thread_mod_date) ? 'Updated by '.$temp_thread_mod_user : false;
    $temp_thread_body = strlen($this_thread_info['thread_body']) > 255 ? substr($this_thread_info['thread_body'], 0, 255).'&hellip;' : $this_thread_info['thread_body'];
    $temp_posts_count = !empty($this_thread_info['post_count']) ? $this_thread_info['post_count'] : 0;
    $temp_thread_timestamp = !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];
    $temp_thread_link = 'community/'.$temp_category_token.'/'.$temp_thread_id.'/'.$temp_thread_token.'/';
            
    // Define the target option text
    $temp_target_thread_author = !empty($this_thread_info['target_user_name_public']) ? $this_thread_info['target_user_name_public'] : $this_thread_info['target_user_name'];
    $temp_target_thread_author_colour = !empty($this_thread_info['target_user_colour_token']) ? $this_thread_info['target_user_colour_token'] : 'none';
    
    // Define if this post is new to the logged in user or not
    $temp_is_new = false;
    // Supress the new flag if thread has already been viewed
    if (!$temp_session_viewed){
      if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID
        //&& $this_thread_info['user_id'] != $this_userinfo['user_id']
        && $this_thread_info['thread_mod_user'] != $this_userinfo['user_id']
        && $temp_thread_timestamp > $this_userinfo['user_backup_login']){
        $temp_is_new = true;
      } elseif ($this_userinfo['user_id'] == MMRPG_SETTINGS_GUEST_ID
        && (($this_time - $temp_thread_timestamp) <= MMRPG_SETTINGS_UPDATE_TIMEOUT)){
        $temp_is_new = true;
      }
    }
    
    ?>
    <div id="thread-<?= $temp_thread_id ?>" data-group="<?= $temp_date_group ?>" class="subbody thread_subbody thread_subbody_small thread_right field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : 'none' ?>" style="text-align: left; margin: 2px 0;">
      <?
      // If this thread has a specific target, display their avatar to the right
      if ($this_thread_info['thread_target'] != 0){
        
        // Define the avatar class and path variables
        $temp_avatar_float = $this_thread_info['user_id'] == $this_userinfo['user_id'] ? 'left' : 'right';
        $temp_avatar_direction = $temp_avatar_float == 'left' ? 'right' : 'left';
        $temp_avatar_frame = $this_thread_info['user_id'] != $this_thread_info['thread_target'] && !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
        $temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
        $temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/intro-field';
        list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
        list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
        $temp_avatar_class = 'avatar avatar_40x40 float float_'.$temp_avatar_float.' ';
        $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
        $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$temp_avatar_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
        
        ?>
        <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 60px 60px;">&nbsp;</div>
        <div class="<?= $temp_avatar_class ?> avatar_userimage avatar_userimage_<?= $temp_avatar_float ?>"><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE ?>);"><?= $temp_thread_author ?></div></div>
        <?
        
        // Define the avatar class and path variables
        //$temp_avatar_frame = '00';
        $temp_avatar_float = $temp_avatar_float == 'left' ? 'right' : 'left';
        $temp_avatar_direction = $temp_avatar_float == 'left' ? 'right' : 'left';
        $temp_avatar_frame =  $this_thread_info['user_id'] == $this_thread_info['thread_target'] && !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
        $temp_avatar_path = !empty($this_thread_info['target_user_image_path']) ? $this_thread_info['target_user_image_path'] : 'robots/mega-man/40';
        $temp_background_path = !empty($this_thread_info['target_user_background_path']) ? $this_thread_info['target_user_background_path'] : 'fields/intro-field';
        list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
        list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
        $temp_avatar_class = 'avatar avatar_40x40 float float_'.$temp_avatar_float.' ';
        $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
        $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$temp_avatar_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
        
        ?>
        <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 60px 60px;">&nbsp;</div>
        <div class="<?= $temp_avatar_class ?> avatar_userimage avatar_userimage_<?= $temp_avatar_float ?>"><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE ?>);"><?= $temp_thread_author ?></div></div>
        <?
      }
      // Otherwise if this is a totally normal community post
      else {
        
        // Define the avatar class and path variables
        $temp_avatar_float = 'left';
        $temp_avatar_direction = 'right';
        $temp_avatar_frame = !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
        $temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
        $temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/intro-field';
        list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
        list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
        $temp_avatar_class = 'avatar avatar_40x40 float float_'.$temp_avatar_float.' ';
        $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
        $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$temp_avatar_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
        
        ?>
        <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 60px 60px;">&nbsp;</div>
        <div class="<?= $temp_avatar_class ?> avatar_userimage avatar_userimage_<?= $temp_avatar_float ?>"><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE ?>);"><?= $temp_thread_author ?></div></div>
        <?
        
      }
      ?>
      <div class="text thread_linkblock thread_linkblock_<?= $this_thread_info['thread_target'] != 0 && $this_thread_info['user_id'] != $this_userinfo['user_id'] ? 'right' : 'left' ?>">
        <a class="link" href="<?= $temp_thread_link ?>"><span><?= $temp_thread_name ?></span></a>
        <div class="info">
          <strong class="player_type player_type_<?= $temp_thread_author_colour ?>"><?= $temp_thread_author ?></strong>
          <?= $this_thread_info['thread_target'] != 0 ? 'to <strong class="player_type player_type_'.$temp_target_thread_author_colour.'">'.$temp_target_thread_author.'</strong>' : '' ?>
          on <strong><?= $temp_thread_date ?></strong>
        </div>
        <div class="count" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
          <a class="comments <?= !empty($temp_posts_count) ? 'field_type field_type_none' : '' ?>" href="<?= $temp_thread_link.(!empty($temp_posts_count) ? '#comment-listing' : '#comment-form') ?>"><?= !empty($temp_posts_count) ? ($temp_posts_count == 1 ? '1 Comment' : $temp_posts_count.' Comments') : 'No Comments' ?></a>
          <?= $temp_is_new ? '<strong class="newpost field_type field_type_electric">New!</strong>' : '' ?>
            <?= !empty($temp_thread_mod_date) ? '<span class="newpost" style="letter-spacing: 0;">'.$temp_thread_mod_date.'</span>' : '' ?>
        </div>
      </div>
    </div>
    <?
    
  }
} else {
  ?>
  <div class="subbody">
  <p class="text">- there are no <?= $this_category_info['category_id'] != 0 ? 'threads' : 'messages' ?> to display -</p>
  </div>
  <?
}

if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
?>