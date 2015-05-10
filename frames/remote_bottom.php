<?
// If this was a script request, immediately kill the session data
if (defined('MMRPG_REMOTE_GAME')){
  $temp_session_key = 'REMOTE_GAME_'.MMRPG_REMOTE_GAME;
  unset($_SESSION[$temp_session_key]);
}
// Unset the database variable
unset($DB);
?>