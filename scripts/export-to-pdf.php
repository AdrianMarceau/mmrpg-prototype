<?php

// EXPORT TO PDF SCRIPT
// This script exports a given on-site page to PDF (and PNG) using wkhtmltopdf/wkhtmltoimage
// It requires exactly two arguments:
//   - src: the relative path of the page to be exported (relative to MMRPG_CONFIG_ROOTURL)
//   - dst: the relative path to export to (relative to MMRPG_CONFIG_ROOTDIR)

// Require the application top file
define('MMRPG_EXCLUDE_GAME_LOGIC', true);
require_once('../top.php');
//exit('<pre>$_REQUEST = '.print_r($_REQUEST, true).'</pre>');

// Define the default values for the export process
$request_format = isset($_REQUEST['return']) && $_REQUEST['return'] === 'json' ? 'json' : 'html';
$show_debug = isset($_REQUEST['debug']) && $_REQUEST['debug'] === 'true' ? true : false;
$system_os = (MMRPG_CONFIG_IS_LIVE === true ? 'linux' : 'macos');
$debug_output = '';
$export_output = array();
$export_output['status'] = 'pending';
$export_output['format'] = $request_format;
$export_output['message'] = '...';
$export_output['data'] = array();

// Define some helper functions for the export process
function cleanPaths($string){
    $string = str_replace(MMRPG_CONFIG_ROOTDIR, '', $string);
    $string = str_replace(MMRPG_CONFIG_ROOTURL, '', $string);
    return $string;
}
function exportKeyGen($src, $dst, $mod){
    return substr(md5(implode('##'.MMRPG_SETTINGS_EXPORTAUTH_SALT.'##', array($src, $dst, $mod))), 6, 6);
}
function exitWithHtml($export_output){
    $debug_markup = !empty($export_output['debug']) ? $export_output['debug'] : ''; unset($export_output['debug']);
    $html_markup = !empty($debug_markup) ? $debug_markup : '<div> <p><b><u>Export to PDF</u></b></p> </div>';
    $html_markup .= '<div> <p><b>$export_output:</b></p> <pre>'.json_encode($export_output, JSON_PRETTY_PRINT).'</pre> </div>';
    $html_markup = cleanPaths($html_markup);
    header('Content-Type: text/html');
    echo('<html>');
    echo('<head><title>Export to PDF</title></head>');
    echo('<body>'.$html_markup.'</body>');
    echo('</html>');
    exit();
}
function exitWithJson($export_output){
    $json_array = $export_output;
    header('Content-Type: application/json');
    echo(json_encode($json_array));
    exit();
}
function endBuffer(){
    global $show_debug, $export_output;
    $debug_output = ob_get_clean();
    if ($show_debug && !empty($debug_output)){ $export_output['debug'] = $debug_output; }
}
function killScript($error_line = 0, $error_message = '', $error_kind = 404){
    global $request_format, $export_output;
    endBuffer();
    $export_output['status'] = 'error';
    $export_output['message'] = $error_message;
    $export_output['data'] = array('line' => $error_line);
    if ($error_kind === 404){ header('HTTP/1.0 404 Not Found'); }
    elseif ($error_kind === 403){ header('HTTP/1.0 403 Forbidden'); }
    elseif ($error_kind === 500){ header('HTTP/1.0 500 Internal Server Error'); }
    if ($request_format === 'html'){ return exitWithHtml($export_output); }
    elseif ($request_format === 'json'){ return exitWithJson($export_output); }
}
function exitScript($return_data = array()){
    global $request_format, $export_output;
    endBuffer();
    $export_output['status'] = 'success';
    $export_output['message'] = 'Export process has completed.';
    $export_output['data'] = $return_data;
    if ($request_format === 'html'){ return exitWithHtml($export_output); }
    elseif ($request_format === 'json'){ return exitWithJson($export_output); }
}

// Start the output buffer to collect debug info
ob_start();

// Print out some debug styles to make writing this script a bit easier
echo('<pre style="color: magenta;">First test on line '.__LINE__.'!</pre>'.PHP_EOL);
echo('<style> html, body { font-family: Arial, sans-serif; font-size: 13px; line-height: 1.6; color: #efefef; background-color: #262626; color: #efefef; margin: 0; padding: 0; }  a { color: #007cf4; text-decoration: underline; } a:hover { color: #3cc0fc; text-decoration: none; } </style>');
echo('<style> pre { max-width: 100%; } pre:not(.array) { white-space: normal; } pre.array > data { display: block; max-height: 200px; overflow: auto; } </style>'.PHP_EOL);
echo('<pre>$request_format = '.print_r($request_format, true).'</pre>'.PHP_EOL);
echo('<pre>$show_debug = '.($show_debug ? 'true' : 'false').'</pre>'.PHP_EOL);
echo('<pre>$system_os = '.print_r($system_os, true).'</pre>'.PHP_EOL);

/* -- COLLECT SOURCE & DESTINATION -- */

// Collect the source and destination arguments from the request
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$export_src = !empty($_REQUEST['src']) && is_string($_REQUEST['src']) ? trim($_REQUEST['src']) : '';
$export_dst = !empty($_REQUEST['dst']) && is_string($_REQUEST['dst']) ? trim($_REQUEST['dst']) : '';
$export_mod = !empty($_REQUEST['mod']) && is_numeric($_REQUEST['mod']) ? intval($_REQUEST['mod']) : time();
$export_auth_key_expected = exportKeyGen($export_src, $export_dst, $export_mod);
$export_auth_key_received = !empty($_REQUEST['auth']) && is_string($_REQUEST['auth']) ? trim($_REQUEST['auth']) : '';
echo('<pre>$export_src = '.print_r($export_src, true).'</pre>'.PHP_EOL);
echo('<pre>$export_dst = '.print_r($export_dst, true).'</pre>'.PHP_EOL);
echo('<pre>$export_mod = '.print_r($export_mod, true).' ('.date('Y-m-d H:i:s', $export_mod).')</pre>'.PHP_EOL);
//echo('<pre>$export_auth_key_expected = '.print_r($export_auth_key_expected, true).'</pre>'.PHP_EOL);
//echo('<pre>$export_auth_key_received = '.print_r($export_auth_key_received, true).'</pre>'.PHP_EOL);
if (empty($export_src) || empty($export_dst)){ killScript(__LINE__, "Both 'src' and 'dst' parameters are required."); }
if (preg_match('/^(https?:)?\/\//i', $export_src) || strpos($export_src, '/') === 0){ killScript(__LINE__, "'src' must be a relative path."); }
if (preg_match('/^(https?:)?\/\//i', $export_dst) || strpos($export_dst, '/') === 0){ killScript(__LINE__, "'dst' must be a relative path."); }
if ($export_auth_key_received !== $export_auth_key_expected){ killScript(__LINE__, "Invalid authorization key provided.", 403); }

/* -- DEFINE EXPORT PARAMETERS -- */

// Define path to the local wkhtmltopdf binary
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$wkhtmltopdf_base_dir = MMRPG_CONFIG_ROOTDIR.'.libs/wkhtmltopdf/';
$wkhtmltopdf_binary_dir = $wkhtmltopdf_base_dir.$system_os.'/';
$wkhtmltopdf_binary_path = $wkhtmltopdf_binary_dir.'wkhtmltopdf';
echo('<pre>$wkhtmltopdf_base_dir = '.print_r($wkhtmltopdf_base_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$wkhtmltopdf_binary_dir = '.print_r($wkhtmltopdf_binary_dir, true).'</pre>'.PHP_EOL);
if (!file_exists($wkhtmltopdf_base_dir)){ killScript(__LINE__, "wkhtmltopdf binary dir not found."); }
if (!file_exists($wkhtmltopdf_base_dir)){ killScript(__LINE__, "wkhtmltopdf binary for {$system_os} not found."); }

// Define path to the local wkhtmltoimage binary
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$wkhtmltoimage_base_dir = $wkhtmltopdf_base_dir;
$wkhtmltoimage_binary_dir = $wkhtmltopdf_binary_dir;
$wkhtmltoimage_binary_path = $wkhtmltoimage_binary_dir.'wkhtmltoimage';
echo('<pre>$wkhtmltoimage_base_dir = '.print_r($wkhtmltoimage_base_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$wkhtmltoimage_binary_dir = '.print_r($wkhtmltoimage_binary_dir, true).'</pre>'.PHP_EOL);
if (!file_exists($wkhtmltoimage_base_dir)){ killScript(__LINE__, "wkhtmltoimage binary dir not found."); }
if (!file_exists($wkhtmltoimage_base_dir)){ killScript(__LINE__, "wkhtmltoimage binary for {$system_os} not found."); }

// Define the base export directory for the thread conversion
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$export_dst = preg_replace('/^\.cache\//', '', $export_dst);
$base_export_dir = MMRPG_CONFIG_ROOTDIR.'.cache/';
$base_export_name = 'export-'.date('Y-m-d-H-i-s');
$base_export_width_px = 1040;
$base_export_width_pt = 780;
$base_export_delay = 2000;
$this_source_url = MMRPG_CONFIG_ROOTURL.$export_src;
$this_export_dir = rtrim($base_export_dir.dirname($export_dst), '/').'/';
$this_export_name = preg_replace('/\.[a-z0-9]{2,4}$/i', '', basename($export_dst));
echo('<pre>$base_export_dir = '.print_r($base_export_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$base_export_name = '.print_r($base_export_name, true).'</pre>'.PHP_EOL);
echo('<pre>$base_export_width_px = '.print_r($base_export_width_px, true).'</pre>'.PHP_EOL);
echo('<pre>$base_export_width_pt = '.print_r($base_export_width_pt, true).'</pre>'.PHP_EOL);
echo('<pre>$base_export_delay = '.print_r($base_export_delay, true).'</pre>'.PHP_EOL);
echo('<pre>$this_export_dir = '.print_r($this_export_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$this_export_name = '.print_r($this_export_name, true).'</pre>'.PHP_EOL);
if (!file_exists($base_export_dir)){ killScript(__LINE__, "Base export path not found at &quot;{$base_export_dir}&quot;."); }
if (!file_exists($this_export_dir)){ recurseMakeDir($this_export_dir, $base_export_dir); }
if (!file_exists($this_export_dir)){ killScript(__LINE__, "Destination export path not found at &quot;{$this_export_dir}&quot;."); }

// Define the export details for the image portion of the process
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$export_img_dir = $this_export_dir;
$export_img_file = $this_export_name.'.png';
$export_img_path = $export_img_dir.$export_img_file;
$export_img_exists = file_exists($export_img_path);
$export_img_fmtime = $export_img_exists ? filemtime($export_img_path) : 0;
echo('<pre>$export_img_dir = '.print_r($export_img_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$export_img_file = '.print_r($export_img_file, true).'</pre>'.PHP_EOL);
echo('<pre>$export_img_path = '.print_r($export_img_path, true).'</pre>'.PHP_EOL);
echo('<pre>$export_img_exists = '.($export_img_exists ? 'true' : 'false').'</pre>'.PHP_EOL);
echo('<pre>$export_img_fmtime = '.print_r($export_img_fmtime, true).($export_img_fmtime ? ' ('.date('Y-m-d H:i:s', $export_img_fmtime).')' : '').'</pre>'.PHP_EOL);

// Define the export details for the document portion of the process
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$export_doc_dir = $this_export_dir;
$export_doc_file = $this_export_name.'.pdf';
$export_doc_path = $export_doc_dir.$export_doc_file;
$export_doc_exists = file_exists($export_doc_path);
$export_doc_fmtime = $export_doc_exists ? filemtime($export_doc_path) : 0;
$export_doc_width = $base_export_width_pt;
$export_doc_height = 9999;
echo('<pre>$export_doc_dir = '.print_r($export_doc_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$export_doc_file = '.print_r($export_doc_file, true).'</pre>'.PHP_EOL);
echo('<pre>$export_doc_path = '.print_r($export_doc_path, true).'</pre>'.PHP_EOL);
echo('<pre>$export_doc_exists = '.($export_doc_exists ? 'true' : 'false').'</pre>'.PHP_EOL);
echo('<pre>$export_doc_fmtime = '.print_r($export_doc_fmtime, true).($export_doc_fmtime ? ' ('.date('Y-m-d H:i:s', $export_doc_fmtime).')' : '').'</pre>'.PHP_EOL);
echo('<pre>$export_doc_width = '.print_r($export_doc_width, true).'</pre>'.PHP_EOL);
echo('<pre>$export_doc_height = '.print_r($export_doc_height, true).'</pre>'.PHP_EOL);

/* -- EXPORT IMAGE AND DOCUMENT -- */

// Check to see if the image and document already exist and if they need to be re-exported
$export_img_required = (!$export_img_exists || ($export_img_exists && $export_img_fmtime < $export_mod)) ? true : false;
$export_doc_required = (!$export_doc_exists || ($export_doc_exists && $export_doc_fmtime < $export_mod)) ? true : false;

// Attempt to export the page as a PNG using the wkhtmltoimage library
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
if ($export_img_required){
    if (file_exists($export_img_path)){ unlink($export_img_path); }
    if (file_exists($export_img_path)){ killScript(__LINE__, "Unable to delete existing image for export!"); }
    $args = array();
    $args[] = escapeshellarg($wkhtmltoimage_binary_path);
    $args[] = '--format PNG';
    $args[] = '--disable-smart-width';
    $args[] = '--width '.$base_export_width_px;
    $args[] = '--javascript-delay '.$base_export_delay;
    //$args[] = '';
    $args[] = escapeshellarg($this_source_url);
    $args[] = escapeshellarg($export_img_path);
    $args = array_filter($args);
    $cmd = implode(' ', $args);
    exec($cmd, $output, $result_code);
    echo('<pre>$cmd = '.print_r($cmd, true).'</pre>'.PHP_EOL);
    echo('<pre>$output = '.print_r($output, true).'</pre>'.PHP_EOL);
    echo('<pre>$result_code = '.print_r($result_code, true).'</pre>'.PHP_EOL);
    $export_img_exists = file_exists($export_img_path);
    if ($result_code !== 0 && !$export_img_exists){ killScript(__LINE__, "wkhtmltoimage conversion failed for thread with code [{$result_code}]!"); }
    else { echo('<pre style="color: lime;">Thread successfully converted to PNG!</pre>'.PHP_EOL); }
    if ($export_img_exists){
        touch($export_img_path, $export_mod);
        echo('<pre>setting img time to '.date('Y-m-d H:i:s', $export_mod).'</pre>'.PHP_EOL);
    }
} else {
    echo('<pre style="color: lime;">Image already exists for thread!</pre>'.PHP_EOL);
}

// With the image successfully generated, we can collect it's width and height for the PDF
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
if ($export_img_exists && $export_doc_required){
    $export_img_info = getimagesize($export_img_path);
    echo('<pre>$export_img_info = <data>'.print_r($export_img_info, true).'</data></pre>'.PHP_EOL);
    if (empty($export_img_info)){ killScript(__LINE__, "Image could not be generated for thread!"); }
    else { echo('<pre style="color: lime;">Image successfully generated for thread!</pre>'.PHP_EOL); }
    $export_img_width = $export_img_info[0];
    $export_img_height = $export_img_info[1];
    echo('<pre>$export_img_width = '.print_r($export_img_width, true).'</pre>'.PHP_EOL);
    echo('<pre>$export_img_height = '.print_r($export_img_height, true).'</pre>'.PHP_EOL);
    $export_doc_height = round($export_img_height * ($export_doc_width / $export_img_width));
    echo('<pre>$export_doc_height = '.print_r($export_doc_height, true).'</pre>'.PHP_EOL);
}

// Attempt to export the thread as a PDF using the wkhtmltopdf binary
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
if ($export_doc_required){
    if (file_exists($export_doc_path)){ unlink($export_doc_path); }
    if (file_exists($export_doc_path)){ killScript(__LINE__, "Unable to delete existing document for export!"); }
    $args = array();
    $args[] = escapeshellarg($wkhtmltopdf_binary_path);
    $args[] = '--enable-local-file-access';
    $args[] = '--disable-smart-shrinking';
    $args[] = '--no-print-media-type';
    $args[] = '--background';
    $args[] = '--margin-top 0';
    $args[] = '--margin-right 0';
    $args[] = '--margin-bottom 0';
    $args[] = '--margin-left 0';
    $args[] = '--page-width '.$export_doc_width.'pt';
    $args[] = '--page-height '.$export_doc_height.'pt';
    $args[] = '--javascript-delay '.$base_export_delay;
    $args[] = '--disable-external-links';
    $args[] = '--load-media-error-handling skip';
    //$args[] = '--log-level error';
    //$args[] = '';
    $args[] = escapeshellarg($this_source_url);
    $args[] = escapeshellarg($export_doc_path);
    $args = array_filter($args);
    $cmd = implode(' ', $args);
    exec($cmd, $output, $result_code);
    echo('<pre>$cmd = '.print_r($cmd, true).'</pre>'.PHP_EOL);
    echo('<pre>$output = '.print_r($output, true).'</pre>'.PHP_EOL);
    echo('<pre>$result_code = '.print_r($result_code, true).'</pre>'.PHP_EOL);
    $export_doc_exists = file_exists($export_doc_path);
    if ($result_code !== 0 && !$export_doc_exists){ killScript(__LINE__, "wkhtmltopdf conversion failed for thread with code [{$result_code}]!"); }
    else { echo('<pre style="color: lime;">Thread successfully converted to PDF!</pre>'.PHP_EOL); }
    echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
    if ($export_doc_exists){
        touch($export_doc_path, $export_mod);
        echo('<pre>setting doc time to '.date('Y-m-d H:i:s', $export_mod).'</pre>'.PHP_EOL);
    }
} else {
    echo('<pre style="color: lime;">Document already exists for thread!</pre>'.PHP_EOL);
}

// ----------- //

echo('<pre style="color: magenta;">Final test on line '.__LINE__.'!</pre>'.PHP_EOL);

// One last time, confirm both the image and document exist before returning
if (!file_exists($export_img_path)){ killScript(__LINE__, "Image could not be generated for thread!", 500); }
if (!file_exists($export_doc_path)){ killScript(__LINE__, "Document could not be generated for thread!", 500); }

// Return the output of the export process
$return_data = array();
$return_data['image'] = $export_img_path;
$return_data['document'] = $export_doc_path;
$return_data['width'] = $export_doc_width;
$return_data['height'] = $export_doc_height;
exitScript($return_data);

// We are done
exit();

?>
