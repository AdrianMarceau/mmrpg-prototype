<?
/*
 * COMMUNITY FORM ACTIONS
 */

// If an action has been submit, process it
$this_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : false;
while ($this_action == 'delete' && !empty($_REQUEST['post_id'])){
  $temp_postinfo = $DB->get_array("SELECT * FROM mmrpg_posts WHERE post_id = '{$_REQUEST['post_id']}' ".(!COMMUNITY_VIEW_MODERATOR ? " AND mmrpg_posts.user_id = {$this_userid}" : ''));
  if (!empty($temp_postinfo)){ $DB->update('mmrpg_posts', array('post_deleted' => 1), "post_id = '{$_REQUEST['post_id']}'"); }
  header('Location: '.$_GET['this_current_url'].'#comment-listing');
  exit();
  break;
}

// If a formaction has been submit, process it
$this_formaction = !empty($_POST['formaction']) ? $_POST['formaction'] : false;
$this_formerrors = array();

// DEBUG
//die('<pre>$_POST: '.print_r($_POST, true).'</pre>');

// POST ACTIONS
while ($this_formaction == 'post'){

  // Define the verified flag
  $verified = true;

  // Collect all submitted form data
  $formdata = array();
  $formdata['category_id'] = isset($_POST['category_id']) ? $_POST['category_id'] : false;
  $formdata['thread_id'] = isset($_POST['thread_id']) ? $_POST['thread_id'] : false;
  $formdata['user_id'] = isset($_POST['user_id']) ? $_POST['user_id'] : false;
  $formdata['post_id'] = isset($_POST['post_id']) ? $_POST['post_id'] : false;
  $formdata['user_ip'] = isset($_POST['user_ip']) ? $_POST['user_ip'] : false;
  $formdata['post_body'] = isset($_POST['post_body']) ? $_POST['post_body'] : false;
  $formdata['post_frame'] = isset($_POST['post_frame']) ? $_POST['post_frame'] : false;
  $formdata['post_time'] = isset($_POST['post_time']) ? $_POST['post_time'] : false;
  $formdata['post_target'] = !empty($_POST['post_target']) ? $_POST['post_target'] : 0;

  // Check to ensure mandatory fields are not left blank
  if ($formdata['category_id'] === false || $formdata['category_id'] === ''){
    $this_formerrors[] = "The Category ID was not provided.";
    $verified = false;
  }
  if (empty($formdata['thread_id'])){
    $this_formerrors[] = "The Thread ID was not provided.";
    $verified = false;
  }
  if (empty($formdata['user_id'])){
    $this_formerrors[] = "The User ID was not provided.";
    $verified = false;
  }
  if ($formdata['post_id'] === false){
    $this_formerrors[] = "The Post ID was not provided.";
    $verified = false;
  }
  elseif ($formdata['post_id'] != 0){
    $temp_postinfo = $DB->get_array("SELECT * FROM mmrpg_posts WHERE post_id = '{$formdata['post_id']}'");
    if (empty($temp_postinfo) && !COMMUNITY_VIEW_MODERATOR){
      $this_formerrors[] = "The provided Post ID belongs to another player.";
      $verified = false;
    }
  }
  if (empty($formdata['user_ip'])){
    $this_formerrors[] = "The User IP was not provided.";
    $verified = false;
  }
  if (empty($formdata['post_body'])){
    $this_formerrors[] = "A comment was not provided.";
    $verified = false;
  }
  elseif (strlen($formdata['post_body']) > MMRPG_SETTINGS_COMMENT_MAXLENGTH && !COMMUNITY_VIEW_MODERATOR){
    $this_formerrors[] = "The provided comment was too long.";
    $verified = false;
  }
  elseif (strlen($formdata['post_body']) < MMRPG_SETTINGS_COMMENT_MINLENGTH && !COMMUNITY_VIEW_MODERATOR){
    $this_formerrors[] = "The provided comment was too short.";
    $verified = false;
  }
  if (empty($formdata['post_frame'])){
    $this_formerrors[] = "The Post Frame was not provided.";
    $verified = false;
  }
  if (empty($formdata['post_time'])){
    $this_formerrors[] = "The Post Time was not provided.";
    $verified = false;
  }
  if ($community_battle_points < MMRPG_SETTINGS_POST_MINPOINTS && !empty($formdata['category_id'])){
    $this_formerrors[] = "You need at least ".number_format(MMRPG_SETTINGS_POST_MINPOINTS, 0, '.', ',')." battle points to post a new comment!.";
    $verified = false;
  }

  if (!empty($formdata['post_time']) && $formdata['post_id'] == 0){
    $temp_postinfo = $DB->get_array("SELECT * FROM mmrpg_posts WHERE post_date = '{$formdata['post_time']}' AND user_id = '{$formdata['user_id']}'");
    if (!empty($temp_postinfo)){
      // Create the error flag to change markup
      //$this_formerrors[] = "This post has already been submit&hellip;";
      define('COMMENT_POST_SUCCESSFUL', true);
      break;
    }
  }

  // If there are any errors, break
  if (!$verified){
    // Create the error flag to change markup
    define('COMMENT_POST_SUCCESSFUL', false);
    break;
  }

  // Sanitize the post's body and frame values to prevent corruption
  $formdata['post_body'] = strip_tags($formdata['post_body']); //preg_replace('/\s+/', ' ', strip_tags($formdata['post_body']));
  $formdata['post_frame'] = preg_match('/^[0-9]{2}$/', $formdata['post_frame']) ? $formdata['post_frame'] : '';

  // Define the is newpost flag variable
  $temp_flag_newpost = empty($formdata['post_id']) || empty($temp_postinfo) ? true : false;
  if ($temp_flag_newpost){ $formdata['post_id'] = $DB->get_value('SELECT MAX(post_id) AS max_id FROM mmrpg_posts', 'max_id') + 1; }

  // Check to see if this is a new post we're working with
  if ($temp_flag_newpost){

    // Define the insert array based on provided data
    $insert_array = array();
    $insert_array['post_id'] = $formdata['post_id'];
    $insert_array['category_id'] = $formdata['category_id'];
    $insert_array['thread_id'] = $formdata['thread_id'];
    $insert_array['user_id'] = $formdata['user_id'];
    $insert_array['user_ip'] = $formdata['user_ip'];
    $insert_array['post_body'] = $formdata['post_body'];
    $insert_array['post_frame'] = $formdata['post_frame'];
    $insert_array['post_date'] = $formdata['post_time'];
    $insert_array['post_mod'] = $formdata['post_time'];
    $insert_array['post_target'] = $formdata['post_target'];

    // Insert this new post into the database
    $DB->insert('mmrpg_posts', $insert_array);

  }
  // Otherwise, if we're editing an existing post
  elseif (!$temp_flag_newpost){

    // Define the update array based on provided data
    $temp_flag_newpost = false;
    $update_array = array();
    $update_array['post_body'] = $formdata['post_body'];
    $update_array['post_frame'] = $formdata['post_frame'];
    $update_array['post_mod'] = $formdata['post_time'];

    // Insert this new post into the database
    $DB->update('mmrpg_posts', $update_array, "post_id = {$formdata['post_id']}");

  }


  // Only update the main thread date if this is a new post
  if ($temp_flag_newpost){

    // Define the update array based on provided data
    $update_array = array();
    $update_array['thread_mod_date'] = $formdata['post_time'];
    $update_array['thread_mod_user'] = $this_userinfo['user_id'];

    // Update the parent thread in the database
    $DB->update('mmrpg_threads', $update_array, "thread_id = {$formdata['thread_id']}");

  }

  // Create the success flag to change markup
  define('COMMENT_POST_SUCCESSFUL', true);
  // If this was a personal message, automatically reload the thread
  if (isset($update_array) || $formdata['post_target'] != 0){
    $temp_threadinfo = $DB->get_array("SELECT * FROM mmrpg_threads WHERE thread_id = {$formdata['thread_id']} LIMIT 1");
    header('Location: '.$this_current_url.'#post-'.$formdata['post_id']);
    exit();
  }

  // Break out of the email loop
  break;
}

// THREAD ACTIONS
while ($this_formaction == 'thread'){

  // Define the verified flag
  $verified = true;

  // Collect all submitted form data
  $formdata = array();
  $formdata['category_id'] = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? $_POST['category_id'] : false;
  $formdata['category_token'] = isset($_POST['category_token']) ? $_POST['category_token'] : false;
  $formdata['new_category_id'] = !empty($_POST['new_category_id']) ? $_POST['new_category_id'] : $formdata['category_id'];
  $formdata['thread_id'] = isset($_POST['thread_id']) ? $_POST['thread_id'] : false;
  $formdata['user_id'] = isset($_POST['user_id']) ? $_POST['user_id'] : false;
  $formdata['user_ip'] = isset($_POST['user_ip']) ? $_POST['user_ip'] : false;
  $formdata['thread_name'] = isset($_POST['thread_name']) ? $_POST['thread_name'] : false;
  $formdata['thread_body'] = isset($_POST['thread_body']) ? $_POST['thread_body'] : false;
  $formdata['thread_frame'] = isset($_POST['thread_frame']) ? $_POST['thread_frame'] : false;
  $formdata['thread_colour'] = isset($_POST['thread_colour']) && $_POST['thread_colour'] != 'none' ? $_POST['thread_colour'] : false;
  $formdata['thread_time'] = isset($_POST['thread_time']) ? $_POST['thread_time'] : false;
  $formdata['thread_published'] = isset($_POST['thread_published']) && $_POST['thread_published'] == 'true' ? true : false;
  $formdata['thread_locked'] = isset($_POST['thread_locked']) && $_POST['thread_locked'] == 'true' ? true : false;
  $formdata['thread_sticky'] = isset($_POST['thread_sticky']) && $_POST['thread_sticky'] == 'true' ? true : false;
  $formdata['thread_target'] = !empty($_POST['thread_target']) ? $_POST['thread_target'] : 0;

  // Define the current maxlength based on board points
  $temp_maxlength = MMRPG_SETTINGS_DISCUSSION_MAXLENGTH;
  if (!empty($this_boardinfo['board_points']) && ceil($this_boardinfo['board_points'] / 1000) > MMRPG_SETTINGS_DISCUSSION_MAXLENGTH){ $temp_maxlength = ceil($this_boardinfo['board_points'] / 1000); }

  // Check to ensure mandatory fields are not left blank
  if (empty($formdata['category_id']) && $formdata['category_id'] !== '0'){
    $this_formerrors[] = "The Category ID was not provided.";
    $verified = false;
  }
  if (empty($formdata['category_token'])){
    $this_formerrors[] = "The Category Token was not provided.";
    $verified = false;
  }
  if (empty($formdata['user_id'])){
    $this_formerrors[] = "The User ID was not provided.";
    $verified = false;
  }
  if (empty($formdata['user_ip'])){
    $this_formerrors[] = "The User IP was not provided.";
    $verified = false;
  }
  if ($formdata['thread_id'] === false){
    $this_formerrors[] = "The Thread ID was not provided.";
    $verified = false;
  }
  elseif ($formdata['thread_id'] != 0){
    $temp_threadinfo = $DB->get_array("SELECT * FROM mmrpg_threads WHERE thread_id = '{$formdata['thread_id']}'");
    if (empty($temp_threadinfo) && !COMMUNITY_VIEW_MODERATOR){
      if ($formdata['category_id'] != 0){ $this_formerrors[] = "The provided Thread ID belongs to another player."; }
      else { $this_formerrors[] = "The provided Message ID belongs to another player."; }
      $verified = false;
    }
  }
  if (empty($formdata['user_ip'])){
    $this_formerrors[] = "The User IP was not provided.";
    $verified = false;
  }
  if (empty($formdata['thread_name'])){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "A discussion name was not provided."; }
    else { $this_formerrors[] = "A message subject was not provided."; }
    $verified = false;
  }
  elseif (strlen($formdata['thread_name']) > MMRPG_SETTINGS_THREADNAME_MAXLENGTH && !COMMUNITY_VIEW_MODERATOR){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "The provided discussion name was too long."; }
    else { $this_formerrors[] = "The provided message subject was too long."; }
    $verified = false;
  }
  elseif (strlen($formdata['thread_name']) < MMRPG_SETTINGS_THREADNAME_MINLENGTH && !COMMUNITY_VIEW_MODERATOR){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "The provided discussion name was too short."; }
    else { $this_formerrors[] = "The provided message subject was too short."; }
    $verified = false;
  }
  if (empty($formdata['thread_body'])){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "A discussion body was not provided."; }
    else { $this_formerrors[] = "A message body was not provided."; }
    $verified = false;
  }
  elseif (strlen($formdata['thread_body']) > $temp_maxlength && !COMMUNITY_VIEW_MODERATOR){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "The provided discussion body was too long."; }
    else { $this_formerrors[] = "The provided message body was too long."; }
    $verified = false;
  }
  elseif (strlen($formdata['thread_body']) < MMRPG_SETTINGS_DISCUSSION_MINLENGTH && !COMMUNITY_VIEW_MODERATOR){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "The provided discussion body was too short."; }
    else { $this_formerrors[] = "The provided message body was too short."; }
    $verified = false;
  }
  if (empty($formdata['thread_frame'])){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "The Thread Frame was not provided."; }
    else { $this_formerrors[] = "The Message Frame was not provided."; }
    $verified = false;
  }
  /*
  if (empty($formdata['thread_colour'])){
    $this_formerrors[] = "The Thread Colour was not provided.";
    $verified = false;
  }
  */
  if (empty($formdata['thread_time'])){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "The Thread Time was not provided."; }
    else { $this_formerrors[] = "The Message Time was not provided."; }
    $verified = false;
  }
if ($community_battle_points < MMRPG_SETTINGS_THREAD_MINPOINTS){
    if ($formdata['category_id'] != 0){ $this_formerrors[] = "You need at least ".number_format(MMRPG_SETTINGS_THREAD_MINPOINTS, 0, '.', ',')." battle points to post a new thread!."; }
    else { $this_formerrors[] = "You need at least ".number_format(MMRPG_SETTINGS_THREAD_MINPOINTS, 0, '.', ',')." battle points to send a new message!."; }
    $verified = false;
  }

  if (!empty($formdata['thread_time']) && $formdata['thread_id'] == 0){
    $temp_threadinfo = $DB->get_array("SELECT * FROM mmrpg_threads WHERE thread_date = '{$formdata['thread_time']}' AND user_id = '{$formdata['user_id']}'");
    if (!empty($temp_threadinfo)){
      // Create the error flag to change markup
      //$this_formerrors[] = "This thread has already been submit&hellip;";
      //die('<pre>$temp_threadinfo:'.print_r($temp_threadinfo, true).'</pre>');
      define('DISCUSSION_POST_SUCCESSFUL', true);
      define('DISCUSSION_POST_SUCCESSFUL_URL', $temp_threadinfo['thread_id'].'/'.$temp_threadinfo['thread_token'].'/');
      header('Location: '.MMRPG_CONFIG_ROOTURL.'community/'.$formdata['category_token'].'/'.DISCUSSION_POST_SUCCESSFUL_URL);
      exit();
      break;
    }
  } elseif (!empty($formdata['thread_id'])){
    $temp_threadinfo = $DB->get_array("SELECT * FROM mmrpg_threads WHERE thread_id = '{$formdata['thread_id']}'");
  }

  // DEBUG
  /*
  die('<pre>'.
    '$_POST: '.print_r($_POST, true)."\n\n".
    '$formdata: '.print_r($formdata, true)."\n\n".
    '$temp_threadinfo: '.print_r($temp_threadinfo, true)."\n\n".
    '</pre>');
  */

  // If there are any errors, break
  if (!$verified){
    // Create the error flag to change markup
    define('DISCUSSION_POST_SUCCESSFUL', false);
    break;
  }

  // Sanitize the thread's body and frame values to prevent corruption
  $formdata['thread_body'] = strip_tags($formdata['thread_body']); //preg_replace('/\s+/', ' ', strip_tags($formdata['thread_body']));
  $formdata['thread_frame'] = preg_match('/^[0-9]{2}$/', $formdata['thread_frame']) ? $formdata['thread_frame'] : '';
  $formdata['thread_colour'] = preg_match('/^[a-z0-9]+$/', $formdata['thread_colour']) ? $formdata['thread_colour'] : '';

  $formdata['thread_name'] = preg_replace('/\s+/', ' ', strip_tags($formdata['thread_name']));
  $formdata['thread_token'] = trim(preg_replace('/([^a-z0-9]+)/i', '-', strtolower($formdata['thread_name'])), '-');

  // Check to see if this is a new thread we're working with
  if (empty($formdata['thread_id'])){

    // Define the insert array based on provided data
    $insert_array = array();
    $insert_array['thread_id'] = $DB->get_value('SELECT MAX(thread_id) AS max_id FROM mmrpg_threads', 'max_id') + 1;
    $insert_array['category_id'] = $formdata['category_id'];
    $insert_array['user_id'] = $formdata['user_id'];
    $insert_array['user_ip'] = $formdata['user_ip'];
    $insert_array['thread_name'] = preg_replace('/\s+/', ' ', strip_tags($formdata['thread_name']));
    $insert_array['thread_token'] = trim(preg_replace('/([^a-z0-9]+)/i', '-', strtolower($insert_array['thread_name'])), '-');
    $insert_array['thread_body'] = $formdata['thread_body'];
    $insert_array['thread_frame'] = $formdata['thread_frame'];
    $insert_array['thread_colour'] = $formdata['thread_colour'];
    $insert_array['thread_date'] = $formdata['thread_time'];
    $insert_array['thread_mod_date'] = $formdata['thread_time'];
    $insert_array['thread_mod_user'] = $formdata['user_id'];
    $insert_array['thread_published'] = $formdata['thread_published'];
    $insert_array['thread_locked'] = $formdata['thread_locked'];
    $insert_array['thread_sticky'] = $formdata['thread_sticky'];
    $insert_array['thread_target'] = $formdata['thread_target'];

    /*
    // DEBUG
    die('<pre>'.
      '$_POST: '.print_r($_POST, true)."\n\n".
      '$formdata: '.print_r($formdata, true)."\n\n".
      '$insert_array: '.print_r($insert_array, true)."\n\n".
      '</pre>');
    */

    //die('<pre>'.print_r($insert_array, true).'</pre>');

    // Insert this new thread into the database
    //die('<pre>$insert_array:'.print_r($insert_array, true).'</pre>');
    $DB->insert('mmrpg_threads', $insert_array);
    $formdata['thread_id'] = $insert_array['thread_id'];

  }
  // Otherwise, if we're editing an existing thread
  elseif (!empty($formdata['thread_id']) && !empty($temp_threadinfo)){

    // Define the update array based on provided data
    $update_array = array();
    $update_array['thread_name'] = preg_replace('/\s+/', ' ', strip_tags($formdata['thread_name']));
    $update_array['thread_token'] = trim(preg_replace('/([^a-z0-9]+)/i', '-', strtolower($update_array['thread_name'])), '-');
    $update_array['thread_body'] = $formdata['thread_body'];
    $update_array['thread_frame'] = $formdata['thread_frame'];
    $update_array['thread_colour'] = $formdata['thread_colour'];
    //$update_array['thread_mod_date'] = $formdata['thread_time'];
    //$update_array['thread_mod_user'] = $formdata['user_id'];
    $update_array['thread_published'] = $formdata['thread_published'];
    $update_array['thread_locked'] = $formdata['thread_locked'];
    $update_array['thread_sticky'] = $formdata['thread_sticky'];

    // DEBUG
    /*
    die('<pre>'.
      '$_POST: '.print_r($_POST, true)."\n\n".
      '$formdata: '.print_r($formdata, true)."\n\n".
      '$update_array: '.print_r($update_array, true)."\n\n".
      '</pre>');
    */

    // Insert this new thread into the database
    //die('<pre>$update_array:'.print_r($update_array, true).'</pre>');
    $DB->update('mmrpg_threads', $update_array, "thread_id = {$formdata['thread_id']}");

  }

  // Create the success flag to change markup
  define('DISCUSSION_POST_SUCCESSFUL', true);
  // Define the new thread URL
  define('DISCUSSION_POST_SUCCESSFUL_URL', $formdata['thread_id'].'/'.$formdata['thread_token'].'/');

  // Redirect to the edited thread if this is an update
  if (isset($update_array) || $formdata['thread_target'] != 0){

    // If the category id changed for this thread upon edit
    if ($formdata['category_id'] != $formdata['new_category_id']){
      /*
      die(
        '$formdata[\'category_id\'] != $formdata[\'new_category_id\'] = '.
        "{$formdata['category_id']} != {$formdata['new_category_id']} = ".
        '$this_categories_index_tokens = <pre>'.print_r($this_categories_index_tokens, true).'</pre>'
        );
      */
      $DB->update('mmrpg_posts', array('category_id' => $formdata['new_category_id']), "thread_id = {$formdata['thread_id']}");
      $DB->update('mmrpg_threads', array('category_id' => $formdata['new_category_id']), "thread_id = {$formdata['thread_id']}");
      $formdata['category_token'] = $this_categories_index_tokens[$formdata['new_category_id']];
    }

    // Redirect to the edited thread directly instead of confirming
    header('Location: '.MMRPG_CONFIG_ROOTURL.'community/'.$formdata['category_token'].'/'.DISCUSSION_POST_SUCCESSFUL_URL);
    exit();

  }


  // Break out of the email loop
  break;
}

?>