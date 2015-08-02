<?
/*
 * INDEX PAGE : COMMUNITY
 */

// Define the viewing as a moderator flag for global use
if (in_array($this_userinfo['role_id'], array(1, 6, 2, 7))){ define('COMMUNITY_VIEW_MODERATOR', true); }
else { define('COMMUNITY_VIEW_MODERATOR', false); }

// Define the SEO variables for this page
$this_seo_title = 'Community | '.$this_seo_title;
$this_seo_description = 'The community forums serve as a place for players and developers to connect and communicate with each other, providing feedback and relaying ideas in a forum-style bulletin board tied directly to player\'s save files.  The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Community Forums';
$this_graph_data['description'] = 'The community forums serve as a place for players and developers to connect and communicate with each other, providing feedback and relaying ideas in a forum-style bulletin board tied directly to player\'s save files.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Community';


/*
 * COLLECT FORMACTIONS
 */

// Collect this player's battle point total
if (empty($_SESSION[mmrpg_game_token()]['DEMO'])){
  $community_battle_points = mmrpg_prototype_battle_points();
} else {
  $community_battle_points = 0;
}

// Collect all the categories from the index
$this_categories_index = mmrpg_website_community_index();
$this_categories_index_tokens = array();
foreach ($this_categories_index AS $token => $info){ $this_categories_index_tokens[$info['category_id']] = $token; }

// Include the community form actions
require_once('pages/page.community_actions.php');


/*
 * COLLECT INDEXES
 */

// Define the view based on available data
$this_current_view = 'index';
if (!empty($this_current_cat)){ $this_current_view = 'category'; }
if ($this_current_id !== false && !empty($this_current_token)){ $this_current_view = 'thread'; }

// Collect all the users from the index (MAYBE DELETE)
//$this_users_query = "SELECT * FROM mmrpg_users ORDER BY user_id ASC";
//$this_users_index = $DB->get_array_list($this_users_query, 'user_id');

// If a specific category has been requested, collect its info
$this_category_info = array();
if (!empty($this_current_cat) && !empty($this_categories_index[$this_current_cat])){
  // Collect this specific category from the database index
  $this_category_info = $this_categories_index[$this_current_cat];
}

// If a specific thread has been requested, collect its info
$this_thread_info = array();
// If this is a new thread, collect default info
if (empty($this_current_id) && $this_current_token == 'new'){
  // Collect this specific thread from the database
  $this_thread_query = "SELECT threads.*
  	FROM mmrpg_threads AS threads
  	LIMIT 1";
    //WHERE threads.thread_id = '0'";
  $this_thread_info = $DB->get_array($this_thread_query);
  foreach ($this_thread_info AS $key => $info){ $this_thread_info[$key] = is_numeric($info) ? 0 : ''; }
  $this_thread_info['user_id'] = $this_userinfo['user_id'];
  $this_thread_info['user_name'] = $this_userinfo['user_name'];
  $this_thread_info['user_name_public'] = $this_userinfo['user_name_public'];
  //die('<pre>'.print_r($this_thread_info, true).'</pre>');

}
elseif (!empty($this_current_id) && !empty($this_current_token)){
  // Collect this specific thread from the database
  $this_thread_query = "SELECT threads.*,
    users.user_id,
    users.role_id,
    roles.role_token,
    roles.role_name,
    roles.role_icon,
    users.user_name,
    users.user_name_clean,
    users.user_name_public,
    users.user_gender,
    users.user_image_path,
    users.user_background_path,
    users.user_colour_token,
    users.user_email_address,
    users.user_website_address,
    users.user_date_created,
    users.user_date_accessed,
    users.user_date_modified,
    users.user_last_login
  	FROM mmrpg_threads AS threads
  	LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
  	LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
  	WHERE threads.thread_id = '{$this_current_id}' AND threads.thread_token = '{$this_current_token}'";
  $this_thread_info = $DB->get_array($this_thread_query);

  // If this thread has not already been viewed this session, increment the counter
  $temp_session_key = 'mmrpg_thread_viewed_'.$this_thread_info['thread_id'];
  if (empty($_SESSION[$temp_session_key])){
    $temp_current_views = $this_thread_info['thread_views'];
    $temp_new_views = $temp_current_views + 1;
    $temp_update_session = $DB->query("UPDATE mmrpg_threads SET thread_views = {$temp_new_views} WHERE thread_id = {$this_thread_info['thread_id']}");
    if (!empty($temp_update_session)){ $this_thread_info['thread_views'] = $temp_new_views; }
    $_SESSION[$temp_session_key] = true;
  }

}

/*
 * DISPLAY SUBPAGE VIEW
 */

// Start generating the page markup
ob_start();

// Start the community tags
echo '<div class="community">';

// If the current view is a specific thread
if ($this_current_view == 'thread'){
  // Check if we're creating a new thread or not
  if (
  (empty($this_current_id) && $this_current_token == 'new') ||
  (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && !empty($_REQUEST['thread_id']))
  ){
    // Require the community thread view
    require_once('page.community_thread_new.php');
  } else {
    // Require the community thread view
    require_once('page.community_thread.php');
  }

}
// Else if the current view is the category listing
elseif ($this_current_view == 'category' && empty($this_current_sub)){
  // Prevent logged-out users from viewing personal messages
  if ($this_userid == MMRPG_SETTINGS_GUEST_ID && ($this_current_cat == 'personal' || $this_current_cat == 'chat')){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'community/');
    exit();
  }

  //die(print_r($this_category_info, true));

  // If this if chat specifically, include separate file
  if ($this_current_cat == 'chat'){

    // Require the community chat view
    require_once('page.community_chat.php');

  }
  // Otherwise, include the normal community category file
  else {

    // Require the community category view
    require_once('page.community_category.php');

  }

}
// Else if the current view is the category listing
elseif ($this_current_view == 'category' && $this_current_sub == 'new'){

  // Require the community category recent view
  require_once('page.community_category_recent.php');

}
// Else if the current view is the community index
elseif ($this_current_view == 'index'){

  // Require the community thread view
  require_once('page.community_index.php');

}

// End the community tags
echo '</div>';

// Collect the buffer and define the page markup
$this_markup_body = trim(ob_get_clean());

?>