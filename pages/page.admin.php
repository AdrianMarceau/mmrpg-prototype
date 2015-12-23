<?php
/*
 * INDEX PAGE : ADMIN
 */

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
//  If this is a ROBOTS editor request, include the appropriate file
elseif ($this_current_sub == 'robots'){
  // Require the admin robots file
  require('page.admin_robots.php');
}
//  If this is a ABILITIES editor request, include the appropriate file
elseif ($this_current_sub == 'abilities'){
  // Require the admin abilities file
  require('page.admin_players.php');
}
//  Otherwise, include the INDEX file if the request is empty or invalid
else {
  // Require the admin index file
  require('page.admin_index.php');
}

?>