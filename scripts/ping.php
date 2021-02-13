<?

// Require the global top files for config + session
define('MMRPG_EXCLUDE_GAME_LOGIC', true);
require_once('../top.php');

// Return a JSON response with the logged-in user ID
header('Content-type: text/json; charset=UTF-8');
echo(json_encode(array(
    'status' => 'success',
    'session_type' => rpg_user::is_member() ? 'member' : 'guest',
    'user_id' => rpg_user::get_current_userid()
    ), JSON_NUMERIC_CHECK));
exit();

?>