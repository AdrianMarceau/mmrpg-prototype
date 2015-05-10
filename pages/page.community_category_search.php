<?

// Define the global variables for posts
$this_posts_array = array();
$this_posts_count = 0;

// Define the empty search variables for posts
$post_search_query = '';
$post_search_array = array();
$post_search_count = 0;

// Define the empty index variables for posts
$post_index_query = '';
$post_index_array = array();
$post_index_array_required = array();
$post_index_count = 0;

// Define the global variables for posts
$this_threads_array = array();
$this_threads_count = 0;

// Define the empty search variables for threads
$thread_search_query = '';
$thread_search_array = array();
$thread_search_count = 0;

// Define the empty index variables for threads
$thread_index_query = '';
$thread_index_array = array();
$thread_index_array_required = array();
$thread_index_count = 0;

// Define the temp filter by labale and default to empty
$temp_filter_data = array();
$temp_filter_result_count = 0;

// Add DISPLAY variables to the filter if provided in the header
if (!empty($_REQUEST['display'])){
  $temp_request = in_array($_REQUEST['display'], array('threads', 'posts')) ? $_REQUEST['display'] : 'threads';
  $temp_filter_data['display'] = $temp_request;
} else {
  $temp_filter_data['display'] = 'threads';
}

// Add LIMIT variables to the filter if provided in the header
if (!empty($_REQUEST['limit'])){
  $temp_request = in_array($_REQUEST['limit'], array('all', 'threads', 'posts')) ? $_REQUEST['limit'] : 'all';
  $temp_filter_data['limit'] = $temp_request;
} else {
  $temp_filter_data['limit'] = 'all';
}

// Add LIMIT variables to the filter if provided in the header
if (!empty($_REQUEST['sort'])){
  $temp_request = in_array($_REQUEST['sort'], array('asc', 'desc')) ? $_REQUEST['sort'] : 'desc';
  $temp_filter_data['sort'] = $temp_request;
} else {
  $temp_filter_data['sort'] = 'desc';
}


// -- SEARCH MATCHING POSTS -- //

// Search the database for matching posts
if (true){

  // Default the filter variable to empty
  $temp_filter_by = '';

  // Add TEXT variables to the filter if provided in the header
  if (!empty($_REQUEST['text'])){
    $temp_text = strip_tags(strtolower($_REQUEST['text']));
    $temp_text = trim(str_replace('/[^-_a-z0-9\s*]+/i', '', $temp_text));
    if (!empty($temp_text)){
      $temp_filter_data['text'] = $temp_text;
      $temp_text = str_replace('*', '%', $temp_text);
      $temp_limit = !empty($_REQUEST['text_limit']) ? $_REQUEST['text_limit'] : 'all';
      $temp_filter_data['text_limit'] = $temp_limit;
      $temp_filter_by .= 'AND (posts.post_body LIKE \'%'.$temp_text.'%\') ';
    }
  }

  // Add PLAYER variables to the filter if provided in the header
  if (!empty($_REQUEST['player'])){
    $temp_player = strip_tags(strtolower($_REQUEST['player']));
    $temp_player = trim(str_replace('/[^-_a-z0-9\s]+/i', '', $temp_player));
    if (!empty($temp_player)){
      $temp_filter_data['player'] = $temp_player;
      $temp_player = str_replace(' ', '%', $temp_player);
      $temp_strict = !empty($_REQUEST['player_strict']) ? true : false;
      $temp_filter_data['player_strict'] = $temp_strict;
      if ($temp_strict == true){ $temp_filter_by .= 'AND (users.user_name LIKE \''.$temp_player.'\' OR users.user_name_public LIKE \''.$temp_player.'\') '; }
      elseif ($temp_strict == false){ $temp_filter_by .= 'AND (users.user_name LIKE \'%'.$temp_player.'%\' OR users.user_name_public LIKE \'%'.$temp_player.'%\') '; }
    }
  }

  // Add CATEGORY variables to the filter if provided in the header
  if (!empty($_REQUEST['category'])){
    $temp_id_includes = array();
    if (!empty($_REQUEST['category']) && !empty($this_categories_index[$_REQUEST['category']])){
      $temp_id_includes[] = $this_categories_index[$_REQUEST['category']]['category_id'];
    }
    /*
    foreach ($_REQUEST['category'] AS $token){
      if (!empty($token) && !empty($this_categories_index[$token])){
        $temp_id_includes[] = $this_categories_index[$token]['category_id'];
      }
    }
    */
    if (!empty($temp_id_includes)){
      $temp_filter_by .= 'AND threads.category_id IN ('.implode(',', $temp_id_includes).') ';
      $temp_filter_data['category'] = $_REQUEST['category'];
    }
  }

  // Collect all the posts matching this query from the database
  if (!empty($temp_filter_data['text']) || !empty($temp_filter_data['player'])){
    $temp_order_by = 'posts.post_date '.strtoupper($temp_filter_data['sort']);
    $post_search_query = "SELECT posts.*, users.*, roles.*, categories.* FROM mmrpg_posts AS posts
      LEFT JOIN mmrpg_users AS users ON posts.user_id = users.user_id
      LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
      LEFT JOIN mmrpg_categories AS categories ON posts.category_id = categories.category_id
      LEFT JOIN mmrpg_threads AS threads ON threads.thread_id = posts.thread_id
      WHERE threads.category_id <> 0 AND posts.post_deleted = 0
      {$temp_filter_by}
      AND 1 = 1
      ORDER BY {$temp_order_by}";
    $post_search_array = $DB->get_array_list($post_search_query);
    $post_search_count = !empty($post_search_array) ? count($post_search_array) : 0;
  }

  // Loop through the posts results and collect required IDs
  if (!empty($post_search_array)){
    foreach ($post_search_array AS $key => $info){
      if (!in_array($info['post_id'], $post_index_array_required)){ $post_index_array_required[] = $info['post_id']; }
      if (!in_array($info['thread_id'], $thread_index_array_required)){ $thread_index_array_required[] = $info['thread_id']; }
      if (!isset($post_index_array[$info['post_id']])){ $post_index_array[$info['post_id']] = $info; }
    }
  }

}


// -- SEARCH MATCHING THREADS -- //

// Search the database for matching posts
if (true){

  // Default the filter variable to empty
  $temp_filter_by = '';

  // Add TEXT variables to the filter if provided in the header
  if (!empty($_REQUEST['text'])){
    $temp_text = strip_tags(strtolower($_REQUEST['text']));
    $temp_text = trim(str_replace('/[^-_a-z0-9\s*]+/i', '', $temp_text));
    if (!empty($temp_text)){
      $temp_filter_data['text'] = $temp_text;
      $temp_text = str_replace('*', '%', $temp_text);
      $temp_limit = !empty($_REQUEST['text_limit']) ? $_REQUEST['text_limit'] : 'all';
      $temp_filter_data['text_limit'] = $temp_limit;
      if ($temp_limit == 'all'){ $temp_filter_by .= 'AND (threads.thread_name LIKE \'%'.$temp_text.'%\' OR threads.thread_body LIKE \'%'.$temp_text.'%\') '; }
      elseif ($temp_limit == 'name'){ $temp_filter_by .= 'AND threads.thread_name LIKE \'%'.$temp_text.'%\' '; }
      elseif ($temp_limit == 'body'){ $temp_filter_by .= 'AND threads.thread_body LIKE \'%'.$temp_text.'%\' '; }
    }
  }

  // Add PLAYER variables to the filter if provided in the header
  if (!empty($_REQUEST['player'])){
    $temp_player = strip_tags(strtolower($_REQUEST['player']));
    $temp_player = trim(str_replace('/[^-_a-z0-9\s]+/i', '', $temp_player));
    if (!empty($temp_player)){
      $temp_filter_data['player'] = $temp_player;
      $temp_player = str_replace(' ', '%', $temp_player);
      $temp_strict = !empty($_REQUEST['player_strict']) ? true : false;
      $temp_filter_data['player_strict'] = $temp_strict;
      if ($temp_strict == true){ $temp_filter_by .= 'AND (users.user_name LIKE \''.$temp_player.'\' OR users.user_name_public LIKE \''.$temp_player.'\') '; }
      elseif ($temp_strict == false){ $temp_filter_by .= 'AND (users.user_name LIKE \'%'.$temp_player.'%\' OR users.user_name_public LIKE \'%'.$temp_player.'%\') '; }
    }
  }

  // Add CATEGORY variables to the filter if provided in the header
  if (!empty($_REQUEST['category'])){
    $temp_id_includes = array();
    if (!empty($_REQUEST['category']) && !empty($this_categories_index[$_REQUEST['category']])){
      $temp_id_includes[] = $this_categories_index[$_REQUEST['category']]['category_id'];
    }
    /*
    foreach ($_REQUEST['category'] AS $token){
      if (!empty($token) && !empty($this_categories_index[$token])){
        $temp_id_includes[] = $this_categories_index[$token]['category_id'];
      }
    }
    */
    if (!empty($temp_id_includes)){
      $temp_filter_by .= 'AND threads.category_id IN ('.implode(',', $temp_id_includes).') ';
      $temp_filter_data['category'] = $_REQUEST['category'];
    }
  }

  // Collect all the threads matching this query from the database
  if (!empty($temp_filter_data['text']) || !empty($temp_filter_data['player'])){
    $temp_order_by = 'threads.thread_date '.strtoupper($temp_filter_data['sort']);
    $thread_search_query = "SELECT threads.*, users.*, users2.*, users3.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
      LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
      LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_name_clean AS mod_user_name_clean, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
      LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_name_clean AS target_user_name_clean, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
      LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
      LEFT JOIN (
        SELECT posts.thread_id, count(1) AS post_count
        FROM mmrpg_posts AS posts
        GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
      WHERE threads.category_id <> 0 AND threads.thread_published = 1
      {$temp_filter_by}
      AND 1 = 1
      ORDER BY {$temp_order_by}";
    $thread_search_array = $DB->get_array_list($thread_search_query);
    $thread_search_count = !empty($thread_search_array) ? count($thread_search_array) : 0;
  }

  // Loop through the threads results and collect required IDs
  if (!empty($thread_search_array)){
    foreach ($thread_search_array AS $key => $info){
      if (!in_array($info['thread_id'], $thread_index_array_required)){ $thread_index_array_required[] = $info['thread_id']; }
      if (!isset($thread_index_array[$info['thread_id']])){ $thread_index_array[$info['thread_id']] = $info; }
    }
  }

}

// -- POPULATE THREAD INDEX -- //

if (count($thread_index_array) < count($thread_index_array_required)){

  // Collect the IDs that are still pending to be collected
  $temp_id_complete = array_keys($thread_index_array);
  $temp_id_pending = array_diff($thread_index_array_required, $temp_id_complete);

  // Collect all the threads matching these IDs from the database
  $temp_order_by = 'threads.thread_date '.strtoupper($temp_filter_data['sort']);
  $temp_filter_by = 'AND threads.thread_id IN ('.implode(',', $temp_id_pending).') ';
  $temp_index_query = "SELECT threads.*, users.*, users2.*, users3.*, roles.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
    LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
    LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
    LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_name_clean AS mod_user_name_clean, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
    LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_name_clean AS target_user_name_clean, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
    LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
    LEFT JOIN (
      SELECT posts.thread_id, count(1) AS post_count
      FROM mmrpg_posts AS posts
      GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
    WHERE threads.category_id <> 0 AND threads.thread_published = 1
    {$temp_filter_by}
    AND 1 = 1
    ORDER BY {$temp_order_by}";
  $temp_index_array = $DB->get_array_list($temp_index_query);
  $temp_index_count = !empty($temp_index_array) ? count($temp_index_array) : 0;

  // If the thread indexes were found, add to main index
  if (!empty($temp_index_array)){
    foreach ($temp_index_array AS $key => $info){
      if (!isset($thread_index_array[$info['thread_id']])){ $thread_index_array[$info['thread_id']] = $info; }
      unset($temp_index_array[$key]);
    }
  }

}

// -- COLLECT USER COUNTS -- //

// Define the array to hold user IDs
$this_user_ids_array = array();

// If the there were posts, loop through and collect users
if (!empty($post_index_array)){
  foreach ($post_index_array AS $key => $info){
    if (!in_array($info['user_id'], $this_user_ids_array)){
      $this_user_ids_array[] = $info['user_id'];
    }
  }
}

// If the there were threads, loop through and collect users
if (!empty($thread_index_array)){
  foreach ($thread_index_array AS $key => $info){
    if (!in_array($info['user_id'], $this_user_ids_array)){
      $this_user_ids_array[] = $info['user_id'];
    }
  }
}


// -- COLLECT DISPLAY POSTS -- //

// If the user has requested to view results as threads
if ($temp_filter_data['display'] == 'posts'){

    // If the user has requested only searching threads
    if ($temp_filter_data['limit'] == 'threads'){

      // Add the searched for threads to the main array
      if (!empty($thread_search_array)){
        foreach ($thread_search_array AS $key => $info){
          $new_key = count($this_posts_array);
          $new_post_array = array();
          foreach ($info AS $field => $value){
            if ($field == 'thread_id'){ $value = $value.'_01'; }
            $new_field = preg_replace('/^thread_/', 'post_', $field);
            $new_post_array[$new_field] = $value;
          }
          $new_post_array['thread_id'] = $info['thread_id'];
          $new_post_array['post_is_thread'] = 1;
          $this_posts_array[] = $new_post_array;
          $this_posts_count++;
        }
      }

    }
    // Else if the user has requested only searching posts
    elseif ($temp_filter_data['limit'] == 'posts'){

      // Add the searched for posts to the main array
      if (!empty($post_search_array)){
        foreach ($post_search_array AS $key => $info){
          $this_posts_array[] = $post_index_array[$info['post_id']];
          $this_posts_count++;
        }
      }

    }
    // Else if the user wants to see everything
    else {

      // Add the searched for threads to the main array
      if (!empty($thread_search_array)){
        foreach ($thread_search_array AS $key => $info){
          $new_key = count($this_posts_array);
          $new_post_array = array();
          foreach ($info AS $field => $value){
            if ($field == 'thread_id'){ $value = $value.'_01'; }
            $new_field = preg_replace('/^thread_/', 'post_', $field);
            $new_post_array[$new_field] = $value;
          }
          $new_post_array['thread_id'] = $info['thread_id'];
          $new_post_array['post_is_thread'] = 1;
          $this_posts_array[] = $new_post_array;
          $this_posts_count++;
        }
      }
      // Add the searched for posts to the main array
      if (!empty($post_search_array)){
        foreach ($post_search_array AS $key => $info){
          $this_posts_array[] = $post_index_array[$info['post_id']];
          $this_posts_count++;
        }
      }

    }

}


// -- COLLECT DISPLAY THREADS -- //

// If the user has requested to view results as threads
if ($temp_filter_data['display'] == 'threads'){

    // If the user has requested only searching threads
    if ($temp_filter_data['limit'] == 'threads'){

      // Add the searched for threads to the main array
      if (!empty($thread_search_array)){
        $temp_thread_ids = array();
        foreach ($thread_search_array AS $key => $info){
          if (in_array($info['thread_id'], $temp_thread_ids)){ continue; }
          $this_threads_array[] = $thread_index_array[$info['thread_id']];
          $temp_thread_ids[] = $info['thread_id'];
          $this_threads_count++;
        }
      }

    }
    // Else if the user has requested only searching posts
    elseif ($temp_filter_data['limit'] == 'posts'){

      // Add the searched for posts to the main array
      if (!empty($post_search_array)){
        $temp_thread_ids = array();
        foreach ($post_search_array AS $key => $info){
          if (in_array($info['thread_id'], $temp_thread_ids)){ continue; }
          $this_threads_array[] = $thread_index_array[$info['thread_id']];
          $temp_thread_ids[] = $info['thread_id'];
          $this_threads_count++;
        }
      }

    }
    // Else if the user wants to see everything
    else {

      // Add the searched for threads to the main array
      if (!empty($thread_search_array)){
        $temp_thread_ids = array();
        foreach ($thread_search_array AS $key => $info){
          if (in_array($info['thread_id'], $temp_thread_ids)){ continue; }
          $this_threads_array[] = $thread_index_array[$info['thread_id']];
          $temp_thread_ids[] = $info['thread_id'];
          $this_threads_count++;
        }
      }
      // Add the searched for posts to the main array
      if (!empty($post_search_array)){
        $temp_thread_ids = array();
        foreach ($post_search_array AS $key => $info){
          if (in_array($info['thread_id'], $temp_thread_ids)){ continue; }
          $this_threads_array[] = $thread_index_array[$info['thread_id']];
          $temp_thread_ids[] = $info['thread_id'];
          $this_threads_count++;
        }
      }

    }

}

/*

die(
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
'<hr /> '.
'');

*/

?>