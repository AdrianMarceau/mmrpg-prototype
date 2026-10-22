<?php

/*
  * --------------------------------
  * -- ANTIBOTS / ANTISPAM SCRIPT --
  * Block requests that feel like spam, bots, or scrapers.
  * Additionally, temp-cache pages that are frequently requested
  * --------------------------------
  */

// Unneccessary if this script is being requested by another same-domain script
$http_referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if (!empty($http_referer) && strpos($http_referer, MMRPG_CONFIG_ROOTURL) === 0){ return; }

// DEBUG DEBUG DEBUG
//function antibot_debug($str){ echo('<pre>'.$str.'</pre>'.PHP_EOL); }
function antibot_debug($str){ error_log($str); }
//antibot_debug('$_GET = '.print_r($_GET, true));
//antibot_debug('$_POST = '.print_r($_POST, true));
//antibot_debug('$_SERVER = '.print_r($_SERVER, true));
//antibot_debug('$_SERVER[REQUEST_URI] = '.print_r($_SERVER['REQUEST_URI'], true));

// Collect the requestor's IP address to see if it's suspicious
$request_ip_address = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
//antibot_debug('$request_ip_address = '.print_r($request_ip_address, true));

// Collect the request path to see if it's suspicious
$request_uri_path = ltrim((!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''), '/');
//antibot_debug('$request_uri_path = '.print_r($request_uri_path, true));


// -- BLOCK SUSPICIOUS REQUESTS -- //
//antibot_debug('(!) Check if this request is suspicious or not');

// Define a flag to see if this request is blocked
$request_is_blocked = false;
$request_blocked_reasons = array();
//antibot_debug('$request_is_blocked = '.($request_is_blocked ? 'true' : 'false'));

// Check to make sure the IP address isn't empty
if (empty($request_ip_address)){
    $request_is_blocked = true;
    $request_blocked_reasons[] = 'empty-ip';
}

// Check to make sure the IP isn't pretending to be localhost on live
if (MMRPG_CONFIG_IS_LIVE === true
    && $request_ip_address === '::1'){
    $request_is_blocked = true;
    $request_blocked_reasons[] = 'fake-localhost';
}

// Check to make sure the URL does not have repeat slashes / recursive URLs
$too_many_slashes = 7;
$num_uri_slashes = substr_count($request_uri_path, '/');
if ($num_uri_slashes >= $too_many_slashes){
    $request_is_blocked = true;
    $request_blocked_reasons[] = 'too-many-slashes';
}

// Check to see if the same user is requesting too many pages in quick succession
// This is a very basic rate-limiting check, and should be improved in the future
$antibot_session_key = 'ANTIBOT';
$antibot_session_array = isset($_SESSION[$antibot_session_key]) ? $_SESSION[$antibot_session_key] : array();
if (!isset($antibot_session_array['times'])){ $antibot_session_array['times'] = array(); }
$current_time = time();
$throttle_time = 5; // seconds
$throttle_requests = 10; // requests
$antibot_session_array['times'][] = $current_time;
//antibot_debug('$antibot_session_array(before) = '.print_r($antibot_session_array, true));
// Remove any timestamps older than the throttle time
$antibot_session_array['times'] = array_filter(
    $antibot_session_array['times'],
    function($time) use ($current_time, $throttle_time){ return ($time >= ($current_time - $throttle_time)); }
    );
//antibot_debug('$antibot_session_array(after) = '.print_r($antibot_session_array, true));
$_SESSION[$antibot_session_key] = $antibot_session_array;
// If the user has made too many requests in the last 5 seconds, block them
if (count($antibot_session_array['times']) > $throttle_requests){
    $request_is_blocked = true;
    $request_blocked_reasons[] = 'too-many-requests';
}

// DEBUG DEBUG DEBUG
//antibot_debug('$request_is_blocked = '.($request_is_blocked ? 'true' : 'false'));
//antibot_debug('$request_blocked_reasons = '.print_r($request_blocked_reasons, true));
//exit('debug exit point in '. basename(__FILE__) . ' on line ' . __LINE__);

// If the request was blocked, abort the entire script now
if ($request_is_blocked){
    // If the request was blocked, set the header and exit
    header('HTTP/1.1 403 Forbidden');
    header('Status: 403 Forbidden');
    header('Content-Type: text/plain; charset=utf-8');
    echo('403 Forbidden'.PHP_EOL);
    echo('Access to this page has been blocked due to suspicious activity.'.PHP_EOL);
    echo('Please return to the home page and try your request again.'.PHP_EOL);
    echo('$_SERVER = '.print_r($_SERVER, true).PHP_EOL);
    die();
}

?>