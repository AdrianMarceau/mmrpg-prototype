<?php

// Require the global top file
require('../../top.php');

// Define the API path w/ version number
define('MMRPG_CONFIG_API_ROOTDIR', MMRPG_CONFIG_ROOTDIR.'api/v2/');

// If a valid kind and script are provided, include the API file, else show an error
if (!empty($_GET['kind'])
    && !empty($_GET['script'])
    && preg_match('/^(robots|mechas|bosses|fields|abilities|items|types)$/', $_GET['kind'])
    && preg_match('/^(tokens|index|data)$/', $_GET['script'])
    && file_exists('scripts/'.$_GET['kind'].'/'.$_GET['script'].'.php')){
    require('scripts/'.$_GET['kind'].'/'.$_GET['script'].'.php');
} else {
    require('api-functions.php');
    critical_api_error('Request Error | Unsupported request or malformed URL structure!', __FILE__, __LINE__);
}

?>