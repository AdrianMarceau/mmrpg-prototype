<?php

/*
  * --------------------------------
  * -- ANTIBOTS / ANTISPAM SCRIPT --
  * Block requests that feel like spam, bots, or scrapers.
  * Additionally, temp-cache pages that are frequently requested
  * --------------------------------
  */

// DEBUG DEBUG DEBUG
function antibot_debug($str){ echo('<pre>'.$str.'</pre>'.PHP_EOL); }
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
    die();
}

?>