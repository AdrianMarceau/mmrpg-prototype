<?php

// CLEAR CACHE SCRIPT
// This script deletes a specific cached file or directory given a path
// and we don't validate because the cache gets cleared all the time

// Require the application top file
define('MMRPG_EXCLUDE_GAME_LOGIC', true);
require_once('../top.php');
//exit('<pre>$_REQUEST = '.print_r($_REQUEST, true).'</pre>');

// Define the default values for the merge process
$request_format = isset($_REQUEST['return']) && $_REQUEST['return'] === 'json' ? 'json' : 'html';
$show_debug = isset($_REQUEST['debug']) && $_REQUEST['debug'] === 'true' ? true : false;
$debug_output = '';
$merge_output = array();
$merge_output['status'] = 'pending';
$merge_output['format'] = $request_format;
$merge_output['message'] = '...';
$merge_output['data'] = array();

// Define some helper functions for the merge process
function cleanPaths($string){
    $string = str_replace(MMRPG_CONFIG_ROOTDIR, '', $string);
    $string = str_replace(MMRPG_CONFIG_ROOTURL, '', $string);
    return $string;
}
function exitWithHtml($merge_output){
    $debug_markup = !empty($merge_output['debug']) ? $merge_output['debug'] : ''; unset($merge_output['debug']);
    $html_markup = !empty($debug_markup) ? $debug_markup : '<div> <p><b><u>Clear Cached File</u></b></p> </div>';
    $html_markup .= '<div> <p><b>$merge_output:</b></p> <pre>'.json_encode($merge_output, JSON_PRETTY_PRINT).'</pre> </div>';
    $html_markup = cleanPaths($html_markup);
    header('Content-Type: text/html');
    echo('<html>');
    echo('<head><title>Clear Cached File</title></head>');
    echo('<body>'.$html_markup.'</body>');
    echo('</html>');
    exit();
}
function exitWithJson($merge_output){
    $json_array = $merge_output;
    header('Content-Type: application/json');
    echo(json_encode($json_array));
    exit();
}
function endBuffer(){
    global $show_debug, $merge_output;
    $debug_output = ob_get_clean();
    if ($show_debug && !empty($debug_output)){ $merge_output['debug'] = $debug_output; }
}
function killScript($error_line = 0, $error_message = '', $error_kind = 404){
    global $request_format, $merge_output;
    endBuffer();
    $merge_output['status'] = 'error';
    $merge_output['message'] = $error_message;
    $merge_output['data'] = array('line' => $error_line);
    if ($error_kind === 404){ header('HTTP/1.0 404 Not Found'); }
    elseif ($error_kind === 403){ header('HTTP/1.0 403 Forbidden'); }
    elseif ($error_kind === 500){ header('HTTP/1.0 500 Internal Server Error'); }
    if ($request_format === 'html'){ return exitWithHtml($merge_output); }
    elseif ($request_format === 'json'){ return exitWithJson($merge_output); }
}
function exitScript($return_data = array()){
    global $request_format, $merge_output;
    endBuffer();
    $merge_output['status'] = 'success';
    $merge_output['message'] = 'Merge process has completed.';
    $merge_output['data'] = $return_data;
    if ($request_format === 'html'){ return exitWithHtml($merge_output); }
    elseif ($request_format === 'json'){ return exitWithJson($merge_output); }
}
function shellCommandExists($cmd, &$debug = ''){
    $which = exec('command -v '.escapeshellarg($cmd).' 2>&1', $output, $code);
    $debug = 'shellCommandExists('.$cmd.')';
    $debug .= PHP_EOL.' => $which: '.print_r($which, true);
    $debug .= PHP_EOL.' => $code: '.print_r($code, true);
    $debug .= PHP_EOL.' => $output: '.print_r($output, true);
    if (empty($which)) { $debug .= PHP_EOL.' return false; //'.__LINE__; return false; }
    elseif (!file_exists($which)) { $debug .= PHP_EOL.' return false; //'.__LINE__; return false; }
    elseif (intval($code) !== 0) { $debug .= PHP_EOL.' return false; //'.__LINE__; return false; }
    $debug .= PHP_EOL.' return true; //'.__LINE__;
    return true;
}

// Start the output buffer to collect debug info
ob_start();

// Print out some debug styles to make writing this script a bit easier
echo('<pre style="color: magenta;">First test on line '.__LINE__.'!</pre>'.PHP_EOL);
echo('<style> html, body { font-family: Arial, sans-serif; font-size: 13px; line-height: 1.6; color: #efefef; background-color: #262626; color: #efefef; margin: 0; padding: 0; }  a { color: #007cf4; text-decoration: underline; } a:hover { color: #3cc0fc; text-decoration: none; } </style>');
echo('<style> pre { max-width: 100%; } pre:not(.array) { white-space: normal; } pre > data { white-space: pre; display: block; max-height: 200px; overflow: auto; } </style>'.PHP_EOL);
echo('<pre>$_REQUEST = <data>'.print_r($_REQUEST, true).'</data></pre>'.PHP_EOL);
echo('<pre>$request_format = '.print_r($request_format, true).'</pre>'.PHP_EOL);
echo('<pre>$show_debug = '.($show_debug ? 'true' : 'false').'</pre>'.PHP_EOL);


/* -- COLLECT & VALIDATE PATH -- */

// Collect the cache path from the request and make sure it exists, else abort
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$cache_file_path = !empty($_REQUEST['path']) && is_string($_REQUEST['path']) ? trim(trim($_REQUEST['path']), '/') : '';
echo('<pre>$cache_file_path = '.print_r($cache_file_path, true).'</pre>'.PHP_EOL);
if (empty($cache_file_path)){ killScript(__LINE__, "Required path argument not provided."); }
$cache_base_dir = MMRPG_CONFIG_ROOTDIR.'.cache/';
$cache_base_url = MMRPG_CONFIG_ROOTURL.'.cache/';
$cache_file_path_dir = $cache_base_dir.$cache_file_path;
$cache_file_path_url = $cache_base_url.$cache_file_path;
echo('<pre>$cache_base_dir = '.print_r($cache_base_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$cache_base_url = '.print_r($cache_base_url, true).'</pre>'.PHP_EOL);
echo('<pre>$cache_file_path_dir = '.print_r($cache_file_path_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$cache_file_path_url = '.print_r($cache_file_path_url, true).'</pre>'.PHP_EOL);
if (empty($cache_base_dir) || !file_exists($cache_base_dir)){ killScript(__LINE__, "Cache directory does not exist."); }
if (empty($cache_file_path_dir) || !file_exists($cache_file_path_dir)){ killScript(__LINE__, "Requested cached file does not exist."); }
$cache_is_file = is_file($cache_file_path_dir);
$cache_is_dir = is_dir($cache_file_path_dir);
$cache_kind = $cache_is_file ? 'file' : ($cache_is_dir ? 'directory' : 'unknown');
if ($cache_is_dir){ killScript(__LINE__, "Requested cached file is a directory, not a file."); }

/* -- DELETE CACHE FILE -- */

// Attempt to delete the requested file or directory
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
if (file_exists($cache_file_path_dir)){
    echo('<pre>Attempting to delete the requested cached file...</pre>'.PHP_EOL);
    $delete_result = @unlink($cache_file_path_dir);
    if ($delete_result === false){ killScript(__LINE__, "Failed to delete the requested cached file."); }
    echo('<pre>Cache file deleted successfully!</pre>'.PHP_EOL);
} else {
    echo('<pre>Cache file does not exist, nothing to delete.</pre>'.PHP_EOL);
}

// Return the output of the export process
$return_data = array();
$return_data['action'] = 'deleted';
$return_data['path'] = $cache_file_path;
exitScript($return_data);

// We are done
exit();


?>