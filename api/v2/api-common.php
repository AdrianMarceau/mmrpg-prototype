<?

// Require the common fuctions file
require('api-functions.php');

// Ensure a request path is provided to the API common file
if (empty($api_request_path)){ critical_api_error('Script Error | API Request Path must be provided!', __FILE__, __LINE__); }

// Define the cache file name based on the API request kind
$api_request_token = false;
if (strstr($api_request_path, '{token}')){
    $api_request_token = !empty($_GET['token']) && preg_match('/^[-_a-z0-9]+$/i', $_GET['token']) ? $_GET['token'] : false;
    if (!empty($api_request_token)){ $api_request_path = str_replace('{token}', $api_request_token, $api_request_path); }
    else { critical_api_error('Request Error | URL Token must be provided for '.$api_request_path.'!', __FILE__, __LINE__); }
}

// Collect any common (but optional) flags for the scripts
$api_include_hidden = !empty($_GET['include_hidden']) && $_GET['include_hidden'] === 'true' ? true : false;
$api_include_incomplete = !empty($_GET['include_incomplete']) && $_GET['include_incomplete'] === 'true' ? true : false;
$api_include_templates = !empty($_GET['include_templates']) && $_GET['include_templates'] === 'true' ? true : false;
if (!empty($_GET['include_all']) && $_GET['include_all'] === 'true'){
    $api_include_hidden = true;
    $api_include_incomplete = true;
    $api_include_templates = true;
    $api_request_path .= '/all';
}

// Define the cache file name and path given everything we've learned
$cache_file_name = 'cache.api_'.str_replace('/', '-', $api_request_path).'.json';
$cache_file_path = MMRPG_CONFIG_CACHE_PATH.'api/'.$cache_file_name;

// If the user has requested we force-clear the cache, we should do so now
if (isset($_GET['refresh']) && $_GET['refresh'] === 'true' && file_exists($cache_file_path)){ unlink($cache_file_path); }

// Check to see if a file already exists and collect its last-modified date
if (file_exists($cache_file_path)){ $cache_file_exists = true; $cache_file_date = date('Ymd-Hi', filemtime($cache_file_path)); }
else { $cache_file_exists = false; $cache_file_date = '00000000-0000'; }

// LOAD FROM CACHE if data exists and is current, otherwise continue so script can refresh and replace
if (MMRPG_CONFIG_CACHE_INDEXES && $cache_file_exists && $cache_file_date >= MMRPG_CONFIG_CACHE_DATE){
    $cache_file_markup = file_get_contents($cache_file_path);
    header('Content-type: text/json; charset=UTF-8');
    echo($cache_file_markup);
    exit();
}

?>