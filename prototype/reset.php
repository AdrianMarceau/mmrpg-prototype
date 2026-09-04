<?php

// RESET PROTOTYPE (NEW GAME)

// Collect a reference to the user object
$this_user = $_SESSION[$session_token]['USER'];

// Reset the game session and reload the page
//$db->log_queries = true;
if (!empty($_REQUEST['full_reset'])
    && $_REQUEST['full_reset'] == 'true'){
    mmrpg_reset_game_session(true, $this_user['userid']);
} else {
    mmrpg_reset_game_session();
}
//$db->log_queries = false;

// Update the appropriate session variables
$_SESSION[$session_token]['USER'] = $this_user;

// Load the save file into memory and overwrite the session
mmrpg_save_game_session();

//header('Location: prototype.php');
unset($db);
exit('success');

?>
