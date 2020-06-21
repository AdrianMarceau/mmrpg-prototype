<?

// Require the application top file
require_once('../top.php');

// Collect the players index if not already populated
if (!isset($mmrpg_index_players) || empty($mmrpg_index_players)){
    $mmrpg_index_players = rpg_player::get_index(true);
}

// Unset the database variable (why?)
unset($db);

// Collect the password string from the URL, if set
$password_string = !empty($_REQUEST['password']) ? $_REQUEST['password'] : false;

// If the password was not empty and is valid, continue
$valid_passwords = '#^([-_a-z0-9]+)$#i';
if (!empty($password_string) && preg_match($valid_passwords, $password_string)){

  // Ensure the user and file details have already been loaded to the session
  if (!empty($_SESSION['GAME']['USER'])){

    // Process the password based on a predefined list
    if (preg_match('#^('.implode('|', array_keys($mmrpg_index_players)).')_#i', $password_string)){

      // UNLOCK ROBOTS
      list($player_token, $robot_token) = explode('_', $password_string);
      $unlock_player_info = $mmrpg_index_players[$player_token];
      $unlock_robot_info = rpg_robot::get_index_info($robot_token);
      mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info);
      exit('success:unlock_'.$player_token.'_'.$robot_token);

    } else {

      // UNLOCK NOTHING!!!
      die('error:password-invalid');

    }

  }

}
// Otherwise, print an error message
else {
  die('error:password-empty');
}

?>