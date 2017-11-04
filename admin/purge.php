<?

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while updating!');  }

// Collect any extra request variables for the purge
$this_cache_date = !empty($_REQUEST['date']) && preg_match('/^([0-9]{8})-([0-9]{2})$/', $_REQUEST['date']) ? $_REQUEST['date'] : MMRPG_CONFIG_CACHE_DATE;
$this_purge_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=purge&date=<?=$this_cache_date?>&limit=<?=$this_purge_limit?>">Purge Inactive Members</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();

/*
  -- VIEW ALL INCOMPLETE SAVE OR USER FILES
  SELECT saves.save_id, users.user_id FROM mmrpg_saves AS saves
  LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
  WHERE users.user_id IS NULL
  UNION
  SELECT saves.save_id, users.user_id FROM mmrpg_saves AS saves
  RIGHT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
  WHERE saves.save_id IS NULL
 */

// Collect the GUEST ID into a normal variable
$guest_userid = MMRPG_SETTINGS_GUEST_ID;

// Delete save files that do not have a user attached
$db->query("DELETE mmrpg_saves FROM mmrpg_saves
  LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_saves.user_id
  WHERE mmrpg_users.user_id IS NULL AND mmrpg_saves.user_id <> {$guest_userid}
  ;");

// Delete users that do not have save files attached
$db->query("DELETE mmrpg_users FROM mmrpg_users
  LEFT JOIN mmrpg_saves ON mmrpg_saves.user_id = mmrpg_users.user_id
  WHERE mmrpg_saves.user_id IS NULL AND mmrpg_users.user_id <> {$guest_userid}
  ;");

// Collect any save files that have a cache date less than the current one
$this_purge_query = "SELECT
  users.*,
  lboard.board_points,
  saves.save_values,
  saves.save_flags,
  saves.save_counters
  FROM mmrpg_users AS users
  LEFT JOIN mmrpg_saves AS saves ON users.user_id = saves.user_id
  INNER JOIN mmrpg_leaderboard AS lboard ON users.user_id = lboard.user_id
  WHERE
  users.user_date_created = users.user_last_login
  AND users.user_id <> {$guest_userid}
  AND (lboard.board_points = 0 OR lboard.board_points IS NULL)
  LIMIT {$this_purge_limit}
  ;";
$this_purge_list = $db->get_array_list($this_purge_query);
//die($this_purge_query);

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$this_purge_list</strong><br />';
$this_page_markup .= 'Query:'.$this_purge_query.'<br />';
$this_page_markup .= 'Count:'.(!empty($this_purge_list) ? count($this_purge_list) : 0).'<br />';
$this_page_markup .= '</p>';

// Loop through each of the player save files
if (!empty($this_purge_list)){
  foreach ($this_purge_list AS $key => $data){

    // Delete everything about this user from the database
    $temp_success = true;
    $delete_databases = array('mmrpg_users', 'mmrpg_saves', 'mmrpg_leaderboard');
    foreach ($delete_databases AS $dbname){
      $temp_success = $db->query("DELETE FROM {$dbname} WHERE user_id = {$data['user_id']}");
      if (!$temp_success){ break; }
    }

    // Delete any files for this user
    //if (file_exists($this_save_dir.$data['save_file_path'].$data['save_file_name'])){ @unlink($this_save_dir.$data['save_file_path'].$data['save_file_name']); }
    //if (file_exists($this_save_dir.$data['save_file_path'])){ @deleteDir($this_save_dir.$data['save_file_path']); }

    // DEBUG
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';

      // Print the debug headers
      $this_page_markup .= '<strong>$this_purge_list['.$key.']</strong><br />';
      $this_page_markup .= 'User ID:'.$data['user_id'].'<br />';
      $this_page_markup .= '<pre>'.print_r($data, true).'</pre><br /><hr /><br />';
      // Print database success
      if ($temp_success === false){ $this_page_markup .= '...Database Failure!'; }
      else { $this_page_markup .= '...Database Success'; }

    $this_page_markup .= '</p><hr />';

    // Reset everything back to default
    mmrpg_reset_game_session();

  }

  // DEBUG
  //$this_page_markup .= '<strong>$this_purge_list</strong><br />';
  //$this_page_markup .= 'Query:'.$this_purge_query.'<br />';
  //$this_page_markup .= 'Count:'.count($this_purge_list).'<br />';
  //$this_page_markup .= '<pre>'.print_r($this_purge_list, true).'</pre><br /><hr /><br />';

}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL INACTIVE SAVE FILES PURGED!</strong></p>';
}

?>