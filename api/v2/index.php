<?php

// Require the global top file
require('../../top.php');

// Set the access control headers to allow others to use it
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

// Define the API path w/ version number
define('MMRPG_CONFIG_API_DIR', 'api/v2/');
define('MMRPG_CONFIG_API_ROOTDIR', MMRPG_CONFIG_ROOTDIR.MMRPG_CONFIG_API_DIR);

// If a valid kind and script are provided, include the API file, else show the readme
if (!empty($_GET['kind'])
    && !empty($_GET['script'])
    && preg_match('/^(players|robots|mechas|bosses|fields|abilities|items|types|skills|music)$/', $_GET['kind'])
    && preg_match('/^(tokens|index|data)$/', $_GET['script'])
    && file_exists('scripts/'.$_GET['kind'].'/'.$_GET['script'].'.php')){
    require('scripts/'.$_GET['kind'].'/'.$_GET['script'].'.php');
} elseif (!empty($_GET['kind']) || !empty($_GET['script'])) {
    require('api-functions.php');
    critical_api_error('Request Error | Unsupported request or malformed URL structure!', __FILE__, __LINE__);
} else {
    header('Content-type: text/plain; charset=UTF-8');
    require('readme.txt');
    exit();
}

?>