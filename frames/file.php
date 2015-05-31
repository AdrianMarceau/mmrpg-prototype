<?
// Require the application top file
require_once('../top.php');

// Include the database index array files
require('../data/database_types.php');
require('../data/database_robots.php');

// Collect the current request type if set
$this_action = !empty($_REQUEST['action']) ? strtolower($_REQUEST['action']) : false;
$allow_fadein = true;
// Define the allowable actions in this script
$allowed_actions = array('save', 'new', 'load', 'unload', 'reset');
// If this action is not allowed, kill the script
if (empty($this_action)){ die('An action must be defined!'); }
elseif (!in_array($this_action, $allowed_actions)){ die(ucfirst($this_action).' is not an allowed action!'); }
else { $allow_fadein = false; }

// Define the variables to hold HTML markup
$html_header_title = '';
$html_header_text = '';
$html_form_fields = '';
$html_form_buttons = '';
$html_form_messages = '';

// Create the has updated flag and default to false
$file_has_updated = false;
$session_token = mmrpg_game_token();

// If the SAVE action was requested
while ($this_action == 'save'){

  // If the form has already been submit, process input
  while (!empty($_POST['submit']) && $_POST['submit'] == 'true'){

    // If the new password field was empty, produce an error
    if (empty($_POST['password_current']) || empty($_POST['password_new'])){
      // Update the form messages markup text
      $html_form_messages .= '<span class="error">(!) A password must be set for your game to be saved.</span>';
      break;
    }
    // Else, collect the current and new password and process them
    elseif (!empty($_POST['password_current']) && !empty($_POST['password_new'])){

      // Trim any whitespace from the passwords
      $_POST['password_current'] = trim($_POST['password_current']);
      $_POST['password_new'] = trim($_POST['password_new']);

      // Collect any profile details
      $user_displayname = !empty($_POST['displayname']) ? preg_replace('/[^-_a-z0-9\.\s]+/i', '', trim($_POST['displayname'])) : '';
      $user_emailaddress = !empty($_POST['emailaddress']) ? preg_replace('/[^-_a-z0-9\.\+@]+/i', '', trim($_POST['emailaddress'])) : '';

      // Check if the password has changed at all
      if (true){

        // Backup the current game's filename for deletion purposes
        $backup_user = $_SESSION[$session_token]['USER'];
        $backup_file = $_SESSION[$session_token]['FILE'];
        $backup_save_filepath = $this_save_dir.$backup_file['path'].$backup_file['name'];

        // Update the current game's user and file info using the new password
        $_SESSION[$session_token]['USER']['displayname'] = $user_displayname;
        $_SESSION[$session_token]['USER']['emailaddress'] = $user_emailaddress;
        $_SESSION[$session_token]['USER']['password'] = $_POST['password_new'];
        $_SESSION[$session_token]['USER']['password_encoded'] = md5($_SESSION[$session_token]['USER']['password']);
        $_SESSION[$session_token]['USER']['imagepath'] = $_POST['imagepath'];
        $_SESSION[$session_token]['USER']['colourtoken'] = $_POST['colourtoken'];
        $_SESSION[$session_token]['FILE']['path'] = $_SESSION[$session_token]['USER']['username_clean'].'/';
        $_SESSION[$session_token]['FILE']['name'] = $_SESSION[$session_token]['USER']['password_encoded'].'.sav';
        $this_save_filepath = $this_save_dir.$_SESSION[$session_token]['FILE']['path'].$_SESSION[$session_token]['FILE']['name'];

      }

    }

    // Save the current game session into the file
    mmrpg_save_game_session($this_save_filepath);
    $this_userinfo = $DB->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");
    $_SESSION['GAME']['USER']['userinfo'] = $this_userinfo;
    // If a game session's info was backup up for deletion
    if (!empty($backup_save_filepath) && $backup_save_filepath != $this_save_filepath){
      @unlink($backup_save_filepath);
    }

    // Update the has updated flag variable
    $file_has_updated = true;

    // Break from the POST loop
    break;

  }

  // Sort the robot index based on robot number
  //$mmrpg_database_robots = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_published = 1 AND robot_flag_complete = 1 AND robot_flag_hidden = 0;", 'robot_token');
  //uasort($mmrpg_database_robots, 'mmrpg_index_sort_robots');
  //die('<pre>$mmrpg_database_robots = '.print_r($mmrpg_database_robots, true).'</pre>');

  // Update the header markup title
  $html_header_title .= 'Save Current Game File';
  // Update the header markup text
  $html_header_text .= 'Your game is saved automatically whenever you return to the main menu and any time you interact with your file through the other sub menus.  Use the form below to make changes to your save file\'s configuration and appearance then click the save button when complete.  Your email address is used for account recovery purposes <em>only</em> and will never be given to third parties or otherwise used for nefarious purposes.';
  // Update the form markup fields
  $html_form_fields .= '<div style="float: left; margin-right: 14px; ">';

    // Username
    $html_form_fields .= '<label class="label label_username" style="width: 100px; ">Username :</label>';
    $html_form_fields .= '<input class="text text_username" type="text" style="width: 230px; " name="username" value="'.htmlentities(trim($_SESSION[$session_token]['USER']['username']), ENT_QUOTES, 'UTF-8', true).'" disabled="disabled" />';

    // Password
    $html_form_fields .= '<label class="label label_password" style="width: 100px; ">Password :</label>';
    $html_form_fields .= '<input class="hidden hidden_password" type="hidden" name="password_current" value="'.htmlentities(trim($_SESSION[$session_token]['USER']['password']), ENT_QUOTES, 'UTF-8', true).'" maxlength="18" />';
    $html_form_fields .= '<input class="hidden hidden_password" type="hidden" name="password_new" value="'.htmlentities(trim($_SESSION[$session_token]['USER']['password']), ENT_QUOTES, 'UTF-8', true).'" maxlength="18" />';
    $html_form_fields .= '<input class="text text_password" type="password" style="width: 230px; " name="password_display" value="'.htmlentities(trim($_SESSION[$session_token]['USER']['password']), ENT_QUOTES, 'UTF-8', true).'" maxlength="18" disabled="disabled" />';

    // Email Address
    $html_form_fields .= '<label class="label label_emailaddress" style="width: 100px; ">Email :</label>';
    $html_form_fields .= '<input class="text text_emailaddress" style="width: 230px; " type="text" name="emailaddress" maxlength="128" value="'.htmlentities(trim(!empty($_SESSION[$session_token]['USER']['emailaddress']) ? $_SESSION[$session_token]['USER']['emailaddress'] : ''), ENT_QUOTES, 'UTF-8', true).'" />';

  $html_form_fields .= '</div>';

  $html_form_fields .= '<div style="float: left;">';

    // Display Name
    $html_form_fields .= '<label class="label label_displayname" style="width: 130px; ">Display Name :</label>';
    $html_form_fields .= '<input class="text text_displayname" style="width: 230px; " type="text" name="displayname" maxlength="18" value="'.htmlentities(trim(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : ''), ENT_QUOTES, 'UTF-8', true).'" />';

    // Player Colour
    $mmrpg_database_type = $mmrpg_index['types'];
    sort($mmrpg_database_type);
    //$html_form_fields .= '<div class="field" style="float: left; width: 46%; min-height: 50px; margin-right: 0; ">';
      $html_form_fields .= '<label class="label label_colourtoken" style="width: 130px; ">Player Colour :</label>';
      $html_colour_options = array();
      $html_colour_options[] = '<option value="">- Select Type -</option>';
      $html_colour_options[] = '<option value="none">Neutral Type</option>';
      // Add all the robot avatars to the list
      foreach ($mmrpg_database_type AS $token => $info){
        if ($token == 'none'){ continue; }
        $html_colour_options[] = '<option value="'.$info['type_token'].'">'.$info['type_name'].' Type</option>';
      }
      // Add player avatars if this is the developer
      if ($this_userinfo['role_id'] == 1){
        $html_colour_options[] = '<option value="energy">Energy Type</option>';
        $html_colour_options[] = '<option value="attack">Attack Type</option>';
        $html_colour_options[] = '<option value="defense">Defense Type</option>';
        $html_colour_options[] = '<option value="speed">Speed Type</option>';
      }
      $temp_select_options = str_replace('value="'.$_SESSION['GAME']['USER']['colourtoken'].'"', 'value="'.$_SESSION['GAME']['USER']['colourtoken'].'" selected="selected"', implode('', $html_colour_options));
      $html_form_fields .= '<select class="select select_colourtoken" style="width: 230px; " name="colourtoken">'.$temp_select_options.'</select>';
    //$html_form_fields .= '</div>';

      // Robot Avatar
      //$html_form_fields .= '<div class="field" style="float: left; width: 46%; min-height: 50px; margin-right: 35px; ">';
      $html_form_fields .= '<label class="label label_imagepath" style="width: 130px; ">Robot Avatar :</label>';
      $html_avatar_options = array();
      $html_avatar_options[] = '<option value="">- Select Robot -</option>';
      // Print the optgroup opening tag
      $temp_optgroup_token = 'MM00';
      $html_avatar_options[] = '<optgroup label="Mega Man Robots">';
      // Add all the robot avatars to the list
      foreach ($mmrpg_database_robots AS $token => $info){
      if ($token == 'robot' || strstr($token, 'copy')){ continue; }
      elseif (isset($info['robot_image']) && $info['robot_image'] == 'robot'){ continue; }
      elseif (isset($info['robot_class']) && $info['robot_class'] == 'mecha'){ continue; }
      elseif (preg_match('/^(DLM)/i', $info['robot_number'])){ continue; }
      elseif (!file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$token.'/')){ continue; }
      if (!mmrpg_prototype_robot_unlocked(false, $token) && $this_userinfo['role_id'] != 1){ continue; }
      //$info = mmrpg_robot::parse_index_info($info);

      // If the game has changed print the new optgroup
      if ($info['robot_game'] != $temp_optgroup_token){
        $temp_optgroup_token = $info['robot_game'];
        if ($temp_optgroup_token == 'MM20'){ $temp_optgroup_name = 'Mega Man Killers'; }
        elseif (preg_match('/^MM([0-9]+)$/', $temp_optgroup_token)){ $temp_optgroup_name = 'Mega Man '.ltrim(str_replace('MM', '', $temp_optgroup_token), '0').' Robots'; }
        else { $temp_optgroup_name = 'Mega Man '.str_replace('MM', '', $temp_optgroup_token).' Robots'; }
        $html_avatar_options[] = '</optgroup>';
        $html_avatar_options[] = '<optgroup label="'.$temp_optgroup_name.'">';
      }

      $size = isset($info['robot_image_size']) ? $info['robot_image_size'] : 40;
      $html_avatar_options[] = '<option value="robots/'.$token.'/'.$size.'">'.$info['robot_number'].' : '.$info['robot_name'].'</option>';

      // Collect the summon count for this robot
      $temp_summon_count = mmrpg_prototype_database_summoned($token);

      // If this is a copy core, add it's type alts
      if (isset($info['robot_core']) && $info['robot_core'] == 'copy'){
        foreach ($mmrpg_index['types'] AS $type_token => $type_info){
          if ($type_token == 'none' || $type_token == 'copy' || (isset($type_info['type_class']) && $type_info['type_class'] == 'special')){ continue; }
          if (!isset($_SESSION['GAME']['values']['battle_items']['item-core-'.$type_token]) && $this_userinfo['role_id'] != 1){ continue; }
          $html_avatar_options[] = '<option value="robots/'.$token.'_'.$type_token.'/'.$size.'">'.$info['robot_number'].' : '.$info['robot_name'].' ('.$type_info['type_name'].' Core)'.'</option>';
        }
      }
      // Otherwise, if this ROBOT MASTER alt skin has been inlocked
      elseif (!empty($info['robot_image_alts'])){
        //die('<pre>$info = '.print_r($info, true).'</pre>');
        // Loop through each of the available alts and print if unlocked
        foreach ($info['robot_image_alts'] AS $key => $this_altinfo){
          // Only print if unlocked or admin
          if ($temp_summon_count >= $this_altinfo['summons']){
            $html_avatar_options[] = '<option value="robots/'.$token.'_'.$this_altinfo['token'].'/'.$size.'">'.$info['robot_number'].' : '.$this_altinfo['name'].'</option>';
          }
        }
      }

    }
      // Add player avatars if this is the developer
      if ($this_userinfo['role_id'] == 1 || $this_userinfo['role_id'] == 6){
        $html_avatar_options[] = '</optgroup>';
        $html_avatar_options[] = '<optgroup label="Mega Man Players">';
        $html_avatar_options[] = '<option value="players/dr-light/40">PLAYER : Dr. Light</option>';
        $html_avatar_options[] = '<option value="players/dr-wily/40">PLAYER : Dr. Wily</option>';
        $html_avatar_options[] = '<option value="players/dr-cossack/40">PLAYER : Dr. Cossack</option>';
      }
      // Add the optgroup closing tag
      $html_avatar_options[] = '</optgroup>';
      $temp_select_options = str_replace('value="'.$_SESSION['GAME']['USER']['imagepath'].'"', 'value="'.$_SESSION['GAME']['USER']['imagepath'].'" selected="selected"', implode('', $html_avatar_options));
      $html_form_fields .= '<select class="select select_imagepath" style="width: 230px; " name="imagepath">'.$temp_select_options.'</select>';
    //$html_form_fields .= '</div>';

  $html_form_fields .= '</div>';

  // Update the form markup buttons
  $html_form_buttons .= '<input class="button button_submit" type="submit" value="Save Game" />';
  $html_form_buttons .= '<input class="button button_reset" type="button" value="Reset Game" onclick="javascript:parent.window.mmrpg_trigger_reset();" />';

  $html_form_buttons .= '<div class="extra_options">';

    // Ensure the player is unlocked
    if (mmrpg_prototype_player_unlocked('dr-light')){
      $html_form_buttons .= '<div class="reset_wrapper wrapper_dr-light">';
        $html_form_buttons .= '<div class="wrapper_header">Dr. Light'.(mmrpg_prototype_complete('dr-light') ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! :D">&hearts;</span>' : '').'</div>';
        if (mmrpg_prototype_battles_complete('dr-light') > 0){ $html_form_buttons .= '<input class="button button_reset button_reset_missions" type="button" value="Reset Missions" onclick="javascript:parent.window.mmrpg_trigger_reset_missions(\'dr-light\', \'Dr. Light\');" />'; }
        else { $html_form_buttons .= '<input class="button button_reset button_reset_missions" type="button" value="Reset Missions" style="text-decoration: line-through;" />'; }
      $html_form_buttons .= '</div>';
    }

    // Ensure the player is unlocked
    if (mmrpg_prototype_player_unlocked('dr-wily')){
      $html_form_buttons .= '<div class="reset_wrapper wrapper_dr-wily">';
        $html_form_buttons .= '<div class="wrapper_header">Dr. Wily'.(mmrpg_prototype_complete('dr-light') ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! >:D">&clubs;</span>' : '').'</div>';
        if (mmrpg_prototype_battles_complete('dr-wily') > 0){ $html_form_buttons .= '<input class="button button_reset button_reset_missions" type="button" value="Reset Missions" onclick="javascript:parent.window.mmrpg_trigger_reset_missions(\'dr-wily\', \'Dr. Wily\');" />'; }
        else { $html_form_buttons .= '<input class="button button_reset button_reset_missions" type="button" value="Reset Missions" style="text-decoration: line-through;" />'; }
      $html_form_buttons .= '</div>';
    }

    // Ensure the player is unlocked
    if (mmrpg_prototype_player_unlocked('dr-cossack')){
      $html_form_buttons .= '<div class="reset_wrapper wrapper_dr-cossack">';
        $html_form_buttons .= '<div class="wrapper_header">Dr. Cossack'.(mmrpg_prototype_complete('dr-light') ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! >:D">&diams;</span>' : '').'</div>';
        if (mmrpg_prototype_battles_complete('dr-cossack') > 0){ $html_form_buttons .= '<input class="button button_reset button_reset_missions" type="button" value="Reset Missions" onclick="javascript:parent.window.mmrpg_trigger_reset_missions(\'dr-cossack\', \'Dr. Cossack\');" />'; }
        else { $html_form_buttons .= '<input class="button button_reset button_reset_missions" type="button" value="Reset Missions" style="text-decoration: line-through;" />'; }
      $html_form_buttons .= '</div>';
    }

  $html_form_buttons .= '</div>';

  //$html_form_buttons .= '<input class="button button_cancel" type="button" value="Cancel" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

  // If the file has been updated, update the data
  if ($file_has_updated){

    // Update the form messages markup text
    $html_form_messages .= '<span class="success">(!) Thank you.  Your game has been saved.</span>';
    // Clear the form fields markup
    $html_form_fields = '<script type="text/javascript"> reloadTimeout = 0; reloadParent = true; </script>';
    // Update the form markup buttons
    $html_form_buttons = ''; //<input class="button button_continue" type="button" value="Continue" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

  }

  // Break from the SAVE loop
  break;
}
// Else, if the NEW action was requested
while ($this_action == 'new'){

  // If the form has already been submit, process input
  while (!empty($_POST['submit']) && $_POST['submit'] == 'true'){

    // If both the username or password are empty, produce an error
    if (empty($_REQUEST['username']) && empty($_REQUEST['emailaddress']) && empty($_REQUEST['password'])){
      $html_form_messages .= '<span class="error">(!) A username, email address, and password must be provided.</span>';
      break;
    }
    // Otherwise, if at least one of them was provided, validate
    else {
      // Trim spaces off the end and beginning
      $_REQUEST['username'] = trim($_REQUEST['username']);
      $temp_username_clean = preg_replace('/[^-a-z0-9]+/i', '', strtolower($_REQUEST['username']));
      $temp_file_path = $this_save_dir.$temp_username_clean.'/';
      $_REQUEST['password'] = trim($_REQUEST['password']);

      // Define the is verfied and default to true
      $html_form_verified = true;
      // Ensure the username is valid
      if (empty($_REQUEST['username'])){
        $html_form_messages .= '<span class="error">(!) A username was not provided.</span>';
        $html_form_verified = false;
      } elseif ($_REQUEST['username'] == 'demo' || file_exists($temp_file_path)){
        $html_form_messages .= '<span class="error">(!) The requested username is already in use - please select another.</span>';
        $html_form_verified = false;
      } elseif (strlen($_REQUEST['username']) < 6 || strlen($_REQUEST['username']) > 18){
        $html_form_messages .= '<span class="error">(!) The username must be between 6 and 18 characters.</span>';
        $html_form_verified = false;
      } elseif (!preg_match('/^[-_a-z0-9\.]+$/i', $_REQUEST['username'])){
        $html_form_messages .= '<span class="error">(!) The username must only contain letters and numbers.</span>';
        $html_form_verified = false;
      }
      // Ensure the email is valid
      if (empty($_REQUEST['emailaddress'])){
        $html_form_messages .= '<span class="error">(!) The email address was not provided.</span>';
        $html_form_verified = false;
      } elseif (!preg_match('/^([^@]+)@([-a-z0-9]+)\.(.*)$/i', $_REQUEST['emailaddress'])){
        $html_form_messages .= '<span class="error">(!) The email address provided was not valid.</span>';
        $html_form_verified = false;
      } elseif (strlen($_REQUEST['emailaddress']) < 6 || strlen($_REQUEST['emailaddress']) > 100){
        $html_form_messages .= '<span class="error">(!) The email address either much too long, or much too short.</span>';
        $html_form_verified = false;
      }
      // Ensure the password is valid
      if (empty($_REQUEST['password'])){
        $html_form_messages .= '<span class="error">(!) The password was not provided.</span>';
        $html_form_verified = false;
      } elseif (strlen($_REQUEST['password']) < 6 || strlen($_REQUEST['password']) > 18){
        $html_form_messages .= '<span class="error">(!) The password must be between 6 and 18 characters.</span>';
        $html_form_verified = false;
      }
      // If not verified, break
      if (!$html_form_verified){ break; }
      // Ensure the captcha code was entered properly
      if (empty($_REQUEST['captcha'])){
        $html_form_messages .= '<span class="error">(!) The security code was not provided.</span>';
        $html_form_verified = false;
      } elseif (empty($_SESSION['captcha'])){
        $html_form_messages .= '<span class="error">(!) Please enable cookies to proceed.</span>';
        $html_form_verified = false;
      } elseif (strtolower($_REQUEST['captcha']) != $_SESSION['captcha']){
        $html_form_messages .= '<span class="error">(!) The security code was not entered correctly.</span>';
        $html_form_verified = false;
      }
      // If not verified, break
      if (!$html_form_verified){ break; }

    }

    // Collect the user details and generate the file ones as well
    $this_user = array();
    $this_user['username'] = trim($_REQUEST['username']);
    $this_user['username_clean'] = preg_replace('/[^-a-z0-9]+/i', '', strtolower($this_user['username']));
    $this_user['password'] = trim($_REQUEST['password']);
    $this_user['password_encoded'] = md5($this_user['password']);
    $this_user['emailaddress'] = trim(strtolower($_REQUEST['emailaddress']));
    $this_file = array();
    $this_file['path'] = $this_user['username_clean'].'/';
    $this_file['name'] = $this_user['password_encoded'].'.sav';

    // Update the save path with the filename
    $this_save_filepath = $this_save_dir.$this_file['path'].$this_file['name'];
    // Update the necessary game session variables
    $_SESSION[$session_token]['DEMO'] = 0;
    $_SESSION[$session_token]['USER'] = $this_user;
    $_SESSION[$session_token]['FILE'] = $this_file;
    // Reset the game session to start fresh
    mmrpg_reset_game_session($this_save_filepath);
    // Save this new game session into the file
    mmrpg_save_game_session($this_save_filepath);
    // Load the save file back into memory and overwrite the session
    mmrpg_load_game_session($this_save_filepath);
    // Update the form markup, then break from the loop
    $file_has_updated = true;

    // Break from the POST loop
    break;

  }

  // Update the header markup title
  $html_header_title .= 'Create New Game File';
  // Update the header markup text
  $html_header_text .= 'Please select a username and password combo for your new save file below. ';
  $html_header_text .= 'You will be required to enter this combo each time you load your save file. ';
  $html_header_text .= 'Usernames and passwords must be between 6 and 18 characters. ';
  $html_header_text .= 'You must be at least 13 years of age to play this game, else have express persmission from a parent or guardian as required by <a href="http://www.coppa.org/" target="_blank">COPPA</a> law. ';

  // Update the form messages with notice text
  if (empty($html_form_messages)){
    $html_form_messages .= '<span class="help">(!) The Username and Password must be from 6 - 18 characters and can <u>only</u> contain letters and numbers!</span>';
    $html_form_messages .= '<span class="help">(!) The Username and Password combo you select for this file <u>cannot</u> be changed, so please remember it!</span>';
  }


  // Update the form markup fields
  $html_form_fields .= '<div class="field" style="float: left; width: 48%; overflow: hidden; ">';
    $html_form_fields .= '<label class="label label_username">Username : *</label>';
    $html_form_fields .= '<input class="text text_username" type="text" name="username" value="'.(!empty($_REQUEST['username']) ? htmlentities(trim($_REQUEST['username']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="18" />';
    $html_form_fields .= '<label class="label label_emailaddress">Email : *</label>';
    $html_form_fields .= '<input class="text text_emailaddress" type="text" name="emailaddress" value="'.(!empty($_REQUEST['emailaddress']) ? htmlentities(trim($_REQUEST['emailaddress']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="100" />';
    $html_form_fields .= '<label class="label label_password">Password : *</label>';
    $html_form_fields .= '<input class="text text_password" type="text" name="password" value="'.(!empty($_REQUEST['password']) ? htmlentities(trim($_REQUEST['password']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="18" />';
    $html_form_fields .= '<div style="float: left; clear: both;">';
      $html_form_fields .= '<input class="checkbox checkbox_ageofconsent" type="checkbox" name="user_flag_ageofconsent" value="true" '.(!empty($_REQUEST['user_flag_ageofconsent']) ? 'checked="checked" ' : '').'" />';
      $html_form_fields .= '<label class="label label_checkbox">I confirm that I am at least 13 years of age</label>';
  $html_form_fields .= '</div>';
  $html_form_fields .= '<div class="field" style="float: left; width: 50%; overflow: hidden; ">';
    $html_form_fields .= '<label class="label label_captcha">Security : *</label>';
    $html_form_fields .= '<div class="field" style="float: left; width: 230px; overflow: hidden; ">';
      $html_form_fields .= '<img class="captcha captcha_image" src="_ext/captcha/captcha.php?'.time().'" width="200" height="70" alt="Security Code" />';
      $html_form_fields .= '<input class="text text_captcha" type="text" name="captcha" style="width: 125px; " value="'.(!empty($_REQUEST['captcha']) ? htmlentities(trim($_REQUEST['captcha']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="18" />';
    $html_form_fields .= '</div>';
  $html_form_fields .= '</div>';



  // Update the form markup buttons
  $html_form_buttons .= '<input class="button button_submit" type="submit" value="New Game" />';
  //$html_form_buttons .= '<input class="button button_cancel" type="button" value="Cancel" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

  // If the file has been updated, update the data
  if ($file_has_updated){

    // Update the form messages markup text
    $html_form_messages = '<span class="success">(!) Thank you.  Your new game has been created.</span>';
    // Clear the form fields markup
    $html_form_fields = '<script type="text/javascript"> reloadParent = true; </script>';
    // Update the form markup buttons
    $html_form_buttons = ''; //<input class="button button_continue" type="button" value="Continue" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

  }

  // Break from the NEW loop
  break;
}
// Else, if the LOAD action was requested
while ($this_action == 'load'){

  // Define the coppa flag
  $html_form_show_coppa = false;

  // If the form has already been submit, process input
  while (!empty($_POST['submit']) && $_POST['submit'] == 'true'){

    // If both the username or password are empty, produce an error
    if (empty($_REQUEST['username']) && empty($_REQUEST['password'])){
      $html_form_messages .= '<span class="error">(!) The username and password were not provided.</span>';
      break;
    }
    // Otherwise, if at least one of them was provided, validate
    else {
      // Trim spaces off the end and beginning
      $_REQUEST['username'] = trim($_REQUEST['username']);
      $_REQUEST['password'] = trim($_REQUEST['password']);
      // Ensure the username is valid
      if (empty($_REQUEST['username'])){
        $html_form_messages .= '<span class="error">(!) The username was not provided.</span>';
        break;
      } elseif ($_REQUEST['username'] == 'demo'){
        $html_form_messages .= '<span class="error">(!) The provided username is not valid.</span>';
        break;
      } elseif (!preg_match('/^[-_a-z0-9\.]+$/i', $_REQUEST['username'])){
        $html_form_messages .= '<span class="error">(!) The provided username contains invalid characters.</span>';
        break;
      }
      // Ensure the password is valid
      if (empty($_REQUEST['password'])){
        $html_form_messages .= '<span class="error">(!) The password was not provided.</span>';
        break;
      }
    }

    // Collect the user details and generate the file ones as well
    $this_user = array();
    $this_user['username'] = trim($_REQUEST['username']);
    $this_user['username_clean'] = preg_replace('/[^-a-z0-9]+/i', '', strtolower($this_user['username']));
    $this_user['password'] = trim($_REQUEST['password']);
    $this_user['password_encoded'] = md5($this_user['password']);
    $this_file = array();
    $this_file['path'] = $this_user['username_clean'].'/';
    $this_file['name'] = $this_user['password_encoded'].'.sav';

    // Check if the requested save file path exists
    $temp_save_filepath = $this_save_dir.$this_file['path'];
    if (file_exists($temp_save_filepath) && is_dir($temp_save_filepath)){

      // The file exists, so let's collect this user's info from teh database
      $temp_database_user = $DB->get_array("SELECT * FROM mmrpg_users WHERE user_name_clean LIKE '{$this_user['username_clean']}'");

      // The file exists, so let's check the password
      $temp_save_filepath .= $this_file['name'];
      if ($this_user['password_encoded'] == $temp_database_user['user_password_encoded']){

        // The password was correct, but let's also make sure the user is old enough
        if (!empty($temp_database_user['user_date_birth']) && !empty($temp_database_user['user_flag_approved'])){

          // The password was correct! Update the session with these credentials
          $_SESSION['GAME']['DEMO'] = 0;
          $_SESSION['GAME']['USER'] = $this_user;
          $_SESSION['GAME']['FILE'] = $this_file;
          // Load the save file into memory and overwrite the session
          $this_save_filepath = $temp_save_filepath;
          mmrpg_load_game_session($this_save_filepath);
          if (empty($_SESSION['GAME']['counters']['battle_points']) || empty($_SESSION['GAME']['values']['battle_rewards'])){
            //die('battle points are empty 2');
            mmrpg_reset_game_session($this_save_filepath);
          }
          mmrpg_save_game_session($this_save_filepath);
          // Update the form markup, then break from the loop
          $file_has_updated = true;
          break;

        }
        // The user has not confirmed their date of birth, produce an error
        else {

          // Define the data of birth checking variables
          $min_dateofbirth = date('Y/m/d', strtotime('13 years ago'));
          $bypass_dateofbirth = false;

          // Allow the test user to bypass age concent, we got an email
          $bypass_dateofbirth_index = array();
          $temp_dateofbirth_index = explode(',', preg_replace('/\s+/', '', MMRPG_COPPA_COMPLIANCE_PERMISSIONS));
          $temp_username_token = trim(strtolower($_REQUEST['username']));
          $temp_email_token = !empty($temp_database_user['user_email_address']) ? trim(strtolower($temp_database_user['user_email_address'])) : 'email@domain.com';
          foreach ($temp_dateofbirth_index AS $string){ list($username, $email) = explode('/', $string); $bypass_dateofbirth_index[strtolower($username)] = strtolower($email); }
          if (!empty($bypass_dateofbirth_index[$temp_username_token]) && $bypass_dateofbirth_index[$temp_username_token] == $temp_email_token){ $bypass_dateofbirth = true; }
          //die('<pre>$bypass_dateofbirth_index = '.print_r($bypass_dateofbirth_index, true).'</pre>');

          // Ensure the dateofbirth is valid
          //die('$min_dateofbirth = '.$min_dateofbirth);
          if (empty($_REQUEST['dateofbirth'])){
            $html_form_messages .= '<span class="error">(!) Your date of birth must be confirmed in order to continue.</span>';
            $html_form_verified = false;
            $html_form_show_coppa = true;
            break;
          } elseif (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $_REQUEST['dateofbirth'])){
            $html_form_messages .= '<span class="error">(!) The date of birth provided was not valid.</span>';
            $html_form_verified = false;
            $html_form_show_coppa = true;
            break;
          } elseif ($_REQUEST['dateofbirth'] > $min_dateofbirth && !$bypass_dateofbirth){
            $html_form_messages .= '<span class="error">(!) You must be at least 13 years of age to use this website or have <a href="images/misc/MMRPG-Prototype_COPPA-Compliance.pdf" target="_blank">a parent or guardian\'s permission</a>.</span>';
            $html_form_verified = false;
            $html_form_show_coppa = true;
            break;
          } elseif ($_REQUEST['dateofbirth'] > $min_dateofbirth && $bypass_dateofbirth){
            $html_form_messages .= '<span class="success">(!) You are under 13 years of age but have obtained parental consent.</span>';
            $html_form_verified = false;
            $html_form_show_coppa = true;
          }

          // The password was correct! Update the session with these credentials
          $_SESSION['GAME']['DEMO'] = 0;
          $_SESSION['GAME']['USER'] = $this_user;
          $_SESSION['GAME']['FILE'] = $this_file;
          // Load the save file into memory and overwrite the session
          $this_save_filepath = $temp_save_filepath;
          mmrpg_load_game_session($this_save_filepath);
          if (empty($_SESSION['GAME']['counters']['battle_points']) || empty($_SESSION['GAME']['values']['battle_rewards'])){
            //die('battle points are empty 1');
            mmrpg_reset_game_session($this_save_filepath);
          }
          // Update the file with the coppa approval flag and birthdate
          $_SESSION['GAME']['USER']['dateofbirth'] = strtotime($_REQUEST['dateofbirth']);
          $_SESSION['GAME']['USER']['approved'] = 1;
          //die('<pre>$_SESSION[GAME][USER] = '.print_r($_SESSION['GAME']['USER'], true).'</pre>');
          mmrpg_save_game_session($this_save_filepath);
          // Update the form markup, then break from the loop
          $file_has_updated = true;
          break;

        }

      }
      // Otherwise, if the password was incorrect
      else {

        // Create an error message and break out of the form
        $html_form_messages .= '<span class="error">(!) The provided password was not correct.</span>';
        break;

      }

    }
    // Otherwise, if the file does not exist, print an error
    else {

      // Create an error message and break out of the form
      $html_form_messages .= '<span class="error">(!) The requested username does not exist.</span>';
      break;

    }

    // Break from the POST loop
    break;

  }

  // Update the header markup title
  $html_header_title .= 'Load Existing Game File';
  // Update the header markup text
  $html_header_text .= 'Please enter the username and password of your save file below. ';
  $html_header_text .= 'Passwords are case-sensitive, though usernames are not.';
  if ($html_form_show_coppa){
    $html_header_text .= '<br /> Your date of birth must now be confirmed in accordance with <a href="http://www.coppa.org/" target="_blank">COPPA</a> guidelines.';
  }
  // Update the form markup fields
  $html_form_fields .= '<label class="label label_username">Username : </label>';
  $html_form_fields .= '<input class="text text_username" type="text" name="username" value="'.(!empty($_REQUEST['username']) ? htmlentities(trim($_REQUEST['username']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="18" />';
  $html_form_fields .= '<label class="label label_password">Password :</label>';
  $html_form_fields .= '<input class="text text_password" type="password" name="password" value="'.(!empty($_REQUEST['password']) ? htmlentities(trim($_REQUEST['password']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="18" />';
  if ($html_form_show_coppa){
    $html_form_fields .= '<div style="clear: both;">';
      $html_form_fields .= '<label class="label label_dateofbirth">Date of Birth : </label>';
      $html_form_fields .= '<input class="text text_dateofbirth" type="text" name="dateofbirth" value="'.(!empty($_REQUEST['dateofbirth']) ? htmlentities(trim($_REQUEST['dateofbirth']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="10" />';
      $html_form_fields .= '<span style="padding-left: 20px; color: #969696; font-size: 10px; letter-spacing: 1px;  ">YYYY/MM/DD</span>';
    $html_form_fields .= '</div>';
  }
  // Update the form markup buttons
  $html_form_buttons .= '<input class="button button_submit" type="submit" value="Load File" />';
  $html_form_buttons .= '<input class="button button_cancel" type="button" value="Cancel" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

  // If the file has been updated, update the data
  if ($file_has_updated){

    // Update the form messages markup text
    $html_form_messages .= '<span class="success">(!) Thank you.  Your game has been loaded.</span>';
    // Clear the form fields markup
    //$html_form_fields = '<script type="text/javascript"> reloadIndex = true; </script>';
    $html_form_fields = '<script type="text/javascript"> reloadParent = true; </script>';
    // Update the form markup buttons
    $html_form_buttons = ''; //<input class="button button_continue" type="button" value="Continue" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

  }

  // Break from the LOAD loop
  break;
}

// Ensure this is not a demo build before doing updates
if (empty($_SESSION[$session_token]['DEMO'])){
  // Require the updates file
  require_once('../admin/file_updates.php');
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title><?= !MMRPG_CONFIG_IS_LIVE ? '@ ' : '' ?><?= ucfirst($this_action) ?> Game | Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/file.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = false;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
// Create the reload parent flag for later
var reloadIndex = false;
var reloadParent = false;
var reloadTimeout = 1000;
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
// Generate the document ready events for this page
$(document).ready(function(){
  // Start playing the file menu music
  //top.mmrpg_music_load('misc/file-menu');

  // Update global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisWindow = $(window);

  // If reload parent has been set to true
  if (reloadIndex == true){
    //alert('about to reload index...');
    var reloadTimeout = setTimeout(function(){
      //alert('reloading index!');
      top.window.location.href = 'prototype/';
      }, reloadTimeout);
    }

  // If reload parent has been set to true
  if (reloadParent == true){
    //alert('about to reload parent...');
    var reloadTimeout = setTimeout(function(){
      //alert('reloading parent!');
      parent.window.location.href = 'prototype.php?wap='+(gameSettings.wapFlag ? 'true' : 'false');
      }, reloadTimeout);
    }

  /*
  // If reload parent/index has been set to true
  if (reloadIndex == true || reloadParent == true){
    alert('about to reload parent...');
    var reloadTimeout = setTimeout(function(){
      alert('reloading parent! '+parent.window.location);
      parent.window.location.href = parent.window.location;
      }, 1000);
    }
  */

  // Fade in the leaderboard screen slowly
  thisBody.waitForImages(function(){
    var tempTimeout = setTimeout(function(){
      <? if ($allow_fadein): ?>
      thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing');
      <? else: ?>
      thisBody.css({opacity:1}).removeClass('hidden');
      <? endif; ?>
      // Let the parent window know the menu has loaded
      parent.prototype_menu_loaded();
      }, 1000);
    }, false, true);

  // Attach resize events to the window
  thisWindow.resize(function(){ windowResizeFrame(); });
  setTimeout(function(){ windowResizeFrame(); }, 1000);
  windowResizeFrame();

  var windowHeight = $(window).height();
  var htmlHeight = $('html').height();
  var htmlScroll = $('html').scrollTop();
  //alert('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

});

// Create the windowResize event for this page
function windowResizeFrame(){

  var windowWidth = thisWindow.width();
  var windowHeight = thisWindow.height();
  var headerHeight = $('.header', thisBody).outerHeight(true);

  var newBodyHeight = windowHeight;
  var newFrameHeight = newBodyHeight - headerHeight;

  if (windowWidth > 800){ thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
  else { thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }

  thisBody.css({height:newBodyHeight+'px'});
  thisPrototype.css({height:newBodyHeight+'px'});

  //console.log('windowWidth = '+windowWidth+'; parentWidth = '+parentWidth+'; thisTypeContainerWidth = '+thisTypeContainerWidth+'; thisStarContainerWidth = '+thisStarContainerWidth+'; ');

}
</script>
</head>
<body id="mmrpg" class="iframe">
  <div id="prototype">

    <form class="menu" action="frames/file.php?action=<?= $this_action ?>" method="post" autocomplete="on">

      <? if(!empty($html_header_text)): ?>
      <span class="header block_1"><?= $html_header_title ?></span>
      <? endif; ?>

      <div class="wrapper">
        <? if(!empty($html_header_text)): ?>
          <p class="intro intro_new"><?= $html_header_text ?></p>
        <? endif; ?>
        <? if(!empty($html_form_messages)): ?>
          <div class="messages_wrapper">
            <?= $html_form_messages ?>
          </div>
        <? endif; ?>
        <div class="fields_wrapper">
          <input type="hidden" name="submit" value="true" />
          <input type="hidden" name="action" value="<?= $this_action ?>" />
          <?= !empty($html_form_fields) ? $html_form_fields : '' ?>
        </div>
        <? if(!empty($html_form_buttons)): ?>
          <div class="buttons_wrapper">
            <?= $html_form_buttons ?>
          </div>
        <? endif; ?>
      </div>

    </form>

  </div>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
// Unset the database variable
unset($DB);
?>
</body>
</html>