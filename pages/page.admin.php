<?php
/*
 * INDEX PAGE : ADMIN
 */

// Prevent lower level users from accessing the admin page
if (!isset($this_userinfo['role_level']) || $this_userinfo['role_level'] < 5){
  header('Location: '.MMRPG_CONFIG_ROOTURL);
  exit();
}

// If we're NOT in the index, collect common data parameters
if (!empty($this_current_sub)){

    // Define the robot header column array for looping through
    // token => array(name, class, width, directions)
    $table_columns = array();

    // Collect the sort properties from the URL if they exist
    $raw_sort = !empty($_GET['sort']) && preg_match('/^([a-z0-9]+)-([a-z0-9]+)$/i', $_GET['sort']) ? $_GET['sort'] : 'id-asc';
    list($sort_column, $sort_direction) = explode('-', strtolower($raw_sort));
    $sort_flags = array('hidden', 'complete', 'published');
    if ($this_current_sub == 'abilities'){ $sort_object = 'ability'; }
    elseif (in_array($this_current_sub, array('mechas', 'robots', 'bosses'))){ $sort_object = 'robot'; }
    else { $sort_object = preg_replace('/e?s$/i', '', $this_current_sub); }
    if (in_array($sort_column, $sort_flags)){ $query_sort = $sort_object.'_flag_'.$sort_column.' '.strtoupper($sort_direction); }
    else { $query_sort = $sort_object.'_'.$sort_column.' '.strtoupper($sort_direction); }
    if ($sort_column == 'core' || $sort_column == 'type'){ $query_sort .= ', '.$sort_object.'_'.$sort_column.'2 '.strtoupper($sort_direction); }

}

//  If this is a USER editor request, include the appropriate file
if ($this_current_sub == 'users'){
  // Require the admin users file
  require('page.admin_users.php');
}
//  If this is a BATTLE editor request, include the appropriate file
elseif ($this_current_sub == 'battles'){
  // Require the admin battles file
  require('page.admin_battles.php');
}
//  If this is a PLAYERS editor request, include the appropriate file
elseif ($this_current_sub == 'players'){
  // Require the admin players file
  require('page.admin_players.php');
}
//  If this is a MECHAS/ROBOTS/BOSSES editor request, include the appropriate file
elseif ($this_current_sub == 'mechas' || $this_current_sub == 'robots' || $this_current_sub == 'bosses'){
  // Require the admin robots file
  require('page.admin_robots.php');
}
//  If this is a ABILITIES editor request, include the appropriate file
elseif ($this_current_sub == 'abilities'){
  // Require the admin abilities file
  require('page.admin_abilities.php');
}
//  If this is a ITEMS editor request, include the appropriate file
elseif ($this_current_sub == 'items'){
  // Require the admin abilities file
  require('page.admin_items.php');
}
//  If this is a FIELDS editor request, include the appropriate file
elseif ($this_current_sub == 'fields'){
  // Require the admin fields file
  require('page.admin_fields.php');
}
//  Otherwise, include the INDEX file if the request is empty or invalid
else {
  // Require the admin index file
  require('page.admin_index.php');
}

?>