<?
// Require the application top file
require_once('../top.php');
// Unset the database variable
unset($DB);

// Collect the password string from the URL, if set
$password_string = !empty($_REQUEST['password']) ? $_REQUEST['password'] : false;

// If the password was not empty and is valid, continue
$valid_passwords = '#^([-_a-z0-9]+)$#i';
if (!empty($password_string) && preg_match($valid_passwords, $password_string)){
  
  // Ensure the user and file details have already been loaded to the session
  if (!empty($_SESSION['GAME']['USER']) && !empty($_SESSION['GAME']['FILE'])){
    
    // Process the password based on a predefined list
    if (preg_match('#^(dr-light|dr-wily|dr-cossack)_#i', $password_string)){ //($password_string == 'dr-light_proto-man'){
      
      // UNLOCK ROBOTS
      list($player_token, $robot_token) = explode('_', $password_string);
      $unlock_player_info = $mmrpg_index['players'][$player_token];
      $unlock_robot_info = mmrpg_robot::get_index_info($robot_token);
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