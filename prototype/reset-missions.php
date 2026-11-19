<?php

// RESET MISSIONS (UNTESTED // DEBUG)

// Reset the appropriate session variables
if (!empty($mmrpg_index_players[$_REQUEST['player']])){
    $temp_session_key = $_REQUEST['player'].'_target-robot-omega_prototype';
    $_SESSION[$session_token]['values']['battle_complete'][$_REQUEST['player']] = array();
    $_SESSION[$session_token]['values']['battle_failure'][$_REQUEST['player']] = array();
    $_SESSION[$session_token]['values'][$temp_session_key] = array();
}

// Load the save file into memory and overwrite the session
mmrpg_save_game_session();

//header('Location: prototype.php');
unset($db);
exit('success');

?>
