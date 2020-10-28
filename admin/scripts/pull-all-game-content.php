<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the global content type index for reference
require_once(MMRPG_CONFIG_CONTENT_PATH.'index.php');

// Require common git functions and variables if not exist already
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/common_functions.php');
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common_variables.php');

// Ensure the user is actually logged in as an admin
if (!defined('MMRPG_CONFIG_ADMIN_MODE')
    || MMRPG_CONFIG_ADMIN_MODE !== true){
    exit_action('error|user not logged in or not admin');
}

// Cache a string of current cookies before we continue
$cookie_args = array();
if (!empty($_COOKIE)){ foreach ($_COOKIE AS $key => $val){ $cookie_args[] = $key.'='.$val; } }
$cookie_string = implode('; ', $cookie_args);
//debug_echo('$_COOKIE = '.print_r($_COOKIE, true));
//debug_echo('$cookie_string = '.print_r($cookie_string, true));

// Define an array to hold all the feedback lines
$request_feedback = array();

// Loop through the content types one-by-one to check for JSON files
session_write_close();
foreach ($content_types_index AS $content_key => $content_info){

    // Collect the content kind as we'll use it a lot
    $content_kind = $content_info['xtoken'];
    //debug_echo('$content_key = '.print_r($content_key, true));
    //debug_echo('$content_info = '.print_r($content_info, true));

    // If this is not an allowed kind, skip now
    if (!in_array($content_kind, $allowed_kinds)){ continue; }

    // Preset the request variables needed for the script
    $post_data = array();
    $post_data['kind'] = $content_kind;
    $post_data['subkind'] = '';
    $post_data['token'] = 'all';
    $post_data['source'] = 'github';
    //debug_echo('$post_data = '.print_r($post_data, true));

    // Preset the script URL we'll be posting to here
    $post_url = MMRPG_CONFIG_ROOTURL.'admin/scripts/pull-game-content.php';
    //debug_echo('$post_url = '.print_r($post_url, true));

    // Init curl connection and send the request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $post_url);
    //curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_COOKIE, $cookie_string);
    $curl_response = curl_exec($curl);
    //debug_echo('$curl_response = '.print_r($curl_response, true));

    // Add the response to the feedback array
    list($curl_status, $curl_message) = explode('|', $curl_response);
    $request_feedback[$curl_status][$post_data['kind']] = trim($curl_message);
    $request_feedback['all'][$post_data['kind']] = trim($curl_message);

}

//debug_echo('$request_feedback = '.print_r($request_feedback, true));

// Loop through request feedback and generate a response for this script
$has_success = !empty($request_feedback['success']) ? true : false;
$has_errors = !empty($request_feedback['error']) ? true : false;
echo(implode(PHP_EOL, $request_feedback['all']));
if ($has_success && !$has_errors){
    exit_action('success|Changes to game content was pulled and updated successfully!');
} elseif (!$has_success && $has_errors){
    exit_action('error|Changes to game content could not be pulled or there were problems updating!');
} else {
    exit_action('success|Some changes to game content were pulled and updated successfully, otherwise not so much!');
}

?>