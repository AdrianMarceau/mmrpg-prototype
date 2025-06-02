<?php

/*
  * --------------------------------
  * -- AUTOCACHE SCRIPT --
  * Temporarily cache pages that are frequently requested
  * --------------------------------
  */

// DEBUG DEBUG DEBUG
//function autocache_debug($str){ echo('<pre>'.$str.'</pre>'.PHP_EOL); }
function autocache_debug($str){ error_log($str); }
//autocache_debug('$_GET = '.print_r($_GET, true));
//autocache_debug('$_POST = '.print_r($_POST, true));
//autocache_debug('$_SERVER = '.print_r($_SERVER, true));
//autocache_debug('$_SERVER[REQUEST_URI] = '.print_r($_SERVER['REQUEST_URI'], true));

// Collect the request path, method, etc. so we can check if this is cacheable
$request_method = !empty($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
$request_uri_path = trim((!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''), '/').'/';
$request_user_auth = (!empty($_SESSION['GAME']['USER']['userid']) && $_SESSION['GAME']['USER']['userid'] > 0) ? true : false;
if (preg_match('/\?([-0-9]+)$/i', $request_uri_path)){ $request_uri_path = preg_replace('/\?([-0-9]+)$/i', '', $request_uri_path); }
//autocache_debug('$request_method = '.print_r($request_method, true));
//autocache_debug('$request_uri_path = '.print_r($request_uri_path, true));
//autocache_debug('$request_user_auth = '.print_r($request_user_auth, true));

// -- CACHE FREQUENT REQUESTS -- //
//autocache_debug('(!) Check if this request is cacheable or not');

// If this is a request for a file instead of a page, we cannot cache it
if (preg_match('/\.([a-z0-9]{2,4})$/i', $request_uri_path)){ return; }

// If this is not a GET request, we should just return now
if ($request_method !== 'GET'){ return; }

// If the user is logged-in, we cannot cache the request
if ($request_user_auth){ return; }

// Define flags for is this request is allowed to be cached, for how long, and if it has already
$request_is_cacheable = false;
$request_cache_duration = 1; // in hours
$request_cache_reasons = array();
//$request_nocache_reasons = array();
//autocache_debug('$request_is_cacheable = '.($request_is_cacheable ? 'true' : 'false'));
//autocache_debug('$request_cache_duration = '.print_r($request_cache_duration, true));
//autocache_debug('$request_cache_reasons = '.print_r((!empty($request_cache_reasons) ? $request_cache_reasons : '[]'), true));
//autocache_debug('$request_nocache_reasons = '.print_r((!empty($request_nocache_reasons) ? $request_nocache_reasons : '[]'), true));

// If this request falls under certain criteria, we can and should be caching it
if ($request_uri_path === '/'){ $request_is_cacheable = true; $request_cache_duration = 6; $request_cache_reasons[] = 'is-home-page'; }
if (strpos($request_uri_path, 'about/') === 0){ $request_is_cacheable = true; $request_cache_duration = 48; $request_cache_reasons[] = 'is-about-page'; }
if (strpos($request_uri_path, 'gallery/') === 0){ $request_is_cacheable = true; $request_cache_duration = 24; $request_cache_reasons[] = 'is-gallery-page'; }
if (strpos($request_uri_path, 'database/') === 0){ $request_is_cacheable = true; $request_cache_duration = 12; $request_cache_reasons[] = 'is-database-page'; }
if (strpos($request_uri_path, 'credits/') === 0){ $request_is_cacheable = true; $request_cache_duration = 72; $request_cache_reasons[] = 'is-credits-page'; }
if (strpos($request_uri_path, 'cookies/') === 0){ $request_is_cacheable = true; $request_cache_duration = 148; $request_cache_reasons[] = 'is-cookies-page'; }
if (strpos($request_uri_path, 'leaderboard/') === 0){ $request_is_cacheable = true; $request_cache_duration = 1; $request_cache_reasons[] = 'is-leaderboard-page'; }
if (strpos($request_uri_path, 'community/') === 0 && strpos($request_uri_path, '/new/') === false){ $request_is_cacheable = true; $request_cache_duration = 3; $request_cache_reasons[] = 'is-community-page'; }
//autocache_debug('$request_is_cacheable = '.($request_is_cacheable ? 'true' : 'false'));
//autocache_debug('$request_cache_duration = '.print_r($request_cache_duration, true));
//autocache_debug('$request_cache_reasons = '.print_r((!empty($request_cache_reasons) ? $request_cache_reasons : '[]'), true));

// If the request is not cacheable, we should stop processing now
if (!$request_is_cacheable){ return; }

// This request is cacheable, so we should process that now
//autocache_debug('(!) This request is cacheable, processing cache now');

// Define the cache file path and name and make sure base dir exists
$cache_base_dir = MMRPG_CONFIG_ROOTDIR . '.cache/pages/';
$cache_file_token = !empty($request_uri_path) ? str_replace('/', '_', trim($request_uri_path, '/')) : 'home';
$cache_file_name = 'cache.'.$cache_file_token.'.html';
$cache_file_path = $cache_base_dir . $cache_file_name;
//autocache_debug('$cache_base_dir = '.print_r($cache_base_dir, true));
//autocache_debug('$cache_file_token = '.print_r($cache_file_token, true));
//autocache_debug('$cache_file_name = '.print_r($cache_file_name, true));
//autocache_debug('$cache_file_path = '.print_r($cache_file_path, true));
if (!is_dir($cache_base_dir)){ mkdir($cache_base_dir, 0755, true); }

// Check if the cache file exists and is not too old (1 hour)
$cache_file_exists = file_exists($cache_file_path) ? true : false;
$cache_file_mtime = $cache_file_exists ? filemtime($cache_file_path) : 0;
$cache_file_age = $cache_file_exists ? (time() - $cache_file_mtime) : -1;
$cache_file_age_limit = $request_cache_duration * 3600; // convert hours to seconds
$cache_file_required = !$cache_file_exists ? true : false;
//autocache_debug('$cache_file_exists = '.($cache_file_exists ? 'true' : 'false'));
//autocache_debug('$cache_file_mtime = '.print_r($cache_file_mtime, true));
//autocache_debug('$cache_file_age = '.print_r($cache_file_age, true));
//autocache_debug('$cache_file_age_limit = '.print_r($cache_file_age_limit, true));
//autocache_debug('$cache_file_required = '.($cache_file_required ? 'true' : 'false'));
define('MMRPG_AUTOCACHE_PAGE', true);
define('MMRPG_AUTOCACHE_PATH', $cache_file_path);
//autocache_debug('MMRPG_AUTOCACHE_PAGE = '.print_r(MMRPG_AUTOCACHE_PAGE, true));
//autocache_debug('MMRPG_AUTOCACHE_PATH = '.print_r(MMRPG_AUTOCACHE_PATH, true));
if ($cache_file_exists && $cache_file_age >= $cache_file_age_limit){
    // If the cache file exists but is too old, delete it
    //autocache_debug('(!) cache file exists but is too old, deleting now');
    unlink($cache_file_path);
    $cache_file_exists = false;
    $cache_file_required = true;
    //autocache_debug('$cache_file_exists(2) = '.($cache_file_exists ? 'true' : 'false'));
    //autocache_debug('$cache_file_required(2) = '.($cache_file_required ? 'true' : 'false'));
}

// DEBUG DEBUG DEBUG
//exit('debug exit point in '. basename(__FILE__) . ' on line ' . __LINE__);

// If we made it this far and the cache file exists, output directly and exit
if ($cache_file_exists){
    //autocache_debug('(!) cache file exists and is fresh, outputting now');
    echo(trim(file_get_contents($cache_file_path)).PHP_EOL);
    echo('<!-- from cached file at '.str_replace(MMRPG_CONFIG_ROOTDIR, '~/', $cache_file_path).' -->'.PHP_EOL);
    exit();
}

// Otherwise, if the cache file doesn't exist yet (or was too old and deleted),
// we can define the save function for the other part of the script to use
//autocache_debug('(!) cache file must be created, defining save function now');
function autocache_start(){
    //autocache_debug('autocache_start() called');
    ob_start();
    return true;
}
function autocache_save(){
    //autocache_debug('autocache_save() called');
    $buffer = ob_get_clean();
    echo(trim($buffer).PHP_EOL);
    if (defined('MMRPG_PAGE_NOT_FOUND') && MMRPG_PAGE_NOT_FOUND === true){ return; }
    if (!defined('MMRPG_AUTOCACHE_PAGE') || MMRPG_AUTOCACHE_PAGE !== true){ return; }
    if (!defined('MMRPG_AUTOCACHE_PATH') || empty(MMRPG_AUTOCACHE_PATH)){ return; }
    if (empty($buffer)){ return; }
    //autocache_debug('autocache_save() writing to '.MMRPG_AUTOCACHE_PATH);
    $file = fopen(MMRPG_AUTOCACHE_PATH, 'w');
    if ($file === false){ return; }
    fwrite($file, $buffer);
    fclose($file);
    return true;
}
autocache_start();
register_shutdown_function('autocache_save');

// DEBUG DEBUG DEBUG
//exit('debug exit point in '. basename(__FILE__) . ' on line ' . __LINE__);

?>