<?

// ==============================
// (!) LEGACY FILE!  DO NOT USE!
// ==============================
die('This file is no longer used!');

// ...

// Require the top file for all admin scripts
require_once('common/top.php');

// Require the git parameters file so we know which pulls are allowed
require_once('common/git-params.php');

// Require the global content type index for reference
require_once(MMRPG_CONFIG_CONTENT_PATH.'index.php');

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
    //debug_echo('$post_url(full) = '.print_r(($post_url.'?'.http_build_query($post_data)), true));

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
    if (MMRPG_CONFIG_IS_LIVE === false){ curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); }
    $curl_response = curl_exec($curl);
    //debug_echo('$curl_response = '.print_r($curl_response, true));

    // If the response was completely empty, there was an issue
    if (empty($curl_response)){
        if (curl_errno($curl)){
            //$error_msg = 'Error message(s): '.preg_replace('/\s+/', ' ', strip_tags(curl_error($curl)));
            $error_msg = curl_error($curl);
        } else {
            $error_msg = 'The cURL response was empty!';
        }
        curl_close($curl);
        exit_action('error|There was an error with the cURL request!', $error_msg);
    }

    // Add the response to the feedback array
    list($curl_status, $curl_message) = explode('|', $curl_response);
    $request_feedback[$curl_status][$post_data['kind']] = trim($curl_message);
    $request_feedback['all'][$post_data['kind']] = trim($curl_message);
    curl_close($curl);

}

//debug_echo('$request_feedback = '.print_r($request_feedback, true));

// Loop through request feedback and generate a response for this script
$has_success = !empty($request_feedback['success']) ? true : false;
$has_errors = !empty($request_feedback['error']) ? true : false;
echo(implode(PHP_EOL, $request_feedback['all']));
if ($has_success && !$has_errors){
    exit_action('success|Changes to game content were pulled and updated successfully!');
} elseif (!$has_success && $has_errors){
    exit_action('error|Changes to game content could not be pulled or there were problems updating!');
} else {
    exit_action('success|Some changes to game content were pulled and updated successfully, otherwise not so much!');
}

?>