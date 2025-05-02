<?php

// MERGE PDF SCRIPT (FROM EXISTING MANIFEST FILE)
// This script assumes a manifest file has already been generated at the appropriate path
// This script also assumes and uses a page-width of 1040px or approx 780pt for the document
// If the manifest does not exist the script will abort, if a file is missing it will skip it

// Require the application top file
define('MMRPG_EXCLUDE_GAME_LOGIC', true);
require_once('../top.php');
//exit('<pre>$_REQUEST = '.print_r($_REQUEST, true).'</pre>');

// Define the default values for the merge process
$request_format = isset($_REQUEST['return']) && $_REQUEST['return'] === 'json' ? 'json' : 'html';
$show_debug = isset($_REQUEST['debug']) && $_REQUEST['debug'] === 'true' ? true : false;
$overwrite_existing = isset($_REQUEST['overwrite']) && $_REQUEST['overwrite'] === 'true' ? true : false;
$system_os = (MMRPG_CONFIG_IS_LIVE === true ? 'linux' : 'macos');
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
    $html_markup = !empty($debug_markup) ? $debug_markup : '<div> <p><b><u>Merge to PDF</u></b></p> </div>';
    $html_markup .= '<div> <p><b>$merge_output:</b></p> <pre>'.json_encode($merge_output, JSON_PRETTY_PRINT).'</pre> </div>';
    $html_markup = cleanPaths($html_markup);
    header('Content-Type: text/html');
    echo('<html>');
    echo('<head><title>Merge to PDF</title></head>');
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
function mergeKeyGen($src, $dst, $mod){
    return substr(md5(implode('##'.MMRPG_SETTINGS_EXPORTAUTH_SALT.'##', array($src, $dst, $mod))), 6, 6);
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
echo('<style> pre { max-width: 100%; } pre:not(.array) { white-space: normal; } pre.array > data { display: block; max-height: 200px; overflow: auto; } </style>'.PHP_EOL);
echo('<pre>$request_format = '.print_r($request_format, true).'</pre>'.PHP_EOL);
echo('<pre>$show_debug = '.($show_debug ? 'true' : 'false').'</pre>'.PHP_EOL);
echo('<pre>$system_os = '.print_r($system_os, true).'</pre>'.PHP_EOL);


/* -- CHECK EXTERNAL DEPENDENCIES -- */

// Check to make sure required packages have been installed, else abort
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$required_packages = array('java', 'pdftk', 'exiftool');
$missing_packages = array();
echo('<pre>Checking for required packages '.implode(', ', $required_packages).'</pre>'.PHP_EOL);
foreach ($required_packages as $package){
    if (shellCommandExists($package, $debug)){ continue; }
    $missing_packages[] = array('package' => $package, 'debug' => $debug);
}
if (!empty($missing_packages)){
    $missing_packages = array();
    echo('<pre style="color: red;">Error: Missing required packages!</pre>'.PHP_EOL);
    foreach ($missing_packages as $package){ echo('<pre style="color: red;">'.$package['package'].'</pre>'.PHP_EOL); }
    killScript(__LINE__, "Missing required packages!");
} else {
    echo('<pre style="color: lime;">All required packages have been installed!</pre>'.PHP_EOL);
}


// -- VALIDATE REQUEST VARIABLES -- //

// Collect the merge path argument from the request
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$merge_base_dir = MMRPG_CONFIG_ROOTDIR.'.cache/';
$merge_base_url = MMRPG_CONFIG_ROOTURL.'.cache/';
$merge_file_path = !empty($_REQUEST['path']) && is_string($_REQUEST['path']) ? trim(trim($_REQUEST['path']), '/') : '';
$merge_file_manifest = 'manifest.json';
$merge_file_manifest_path = $merge_base_dir.$merge_file_path.'/'.$merge_file_manifest;
echo('<pre>$merge_base_dir = '.print_r($merge_base_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$merge_base_url = '.print_r($merge_base_url, true).'</pre>'.PHP_EOL);
echo('<pre>$merge_file_path = '.print_r($merge_file_path, true).'</pre>'.PHP_EOL);
echo('<pre>$merge_file_manifest = '.print_r($merge_file_manifest, true).'</pre>'.PHP_EOL);
echo('<pre>$merge_file_manifest_path = '.print_r($merge_file_manifest_path, true).'</pre>'.PHP_EOL);
if (empty($merge_base_dir) || !file_exists($merge_base_dir)){ killScript(__LINE__, "Merge base path does not exist."); }
if (empty($merge_file_path) || !file_exists($merge_base_dir.$merge_file_path)){ killScript(__LINE__, "Merge file path required to start process."); }
if (empty($merge_file_manifest) || !file_exists($merge_file_manifest_path)){ killScript(__LINE__, "Merge file manfiest required to start process."); }

// Define the export details and check to make sure the file doesn't already exist
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$merge_export_dir = $merge_base_dir.$merge_file_path.'/';
$merge_export_url = $merge_base_url.$merge_file_path.'/';
$merge_export_file_name = 'export.pdf';
//$merge_export_file_name = 'mmrpg-export_'.str_replace('/', '-', $merge_file_path).'_'.date('Ymd-Hi', $manifest_files_last_mod).'.pdf';
//$merge_export_file_name = date('Ymd-Hi', $manifest_files_last_mod).'.pdf';
$merge_export_file_dir = $merge_export_dir.$merge_export_file_name;
$merge_export_file_url = $merge_export_url.$merge_export_file_name;
echo('<pre>$merge_export_dir = '.print_r($merge_export_dir, true).'</pre>'.PHP_EOL);
echo('<pre>$merge_export_file_name = '.print_r($merge_export_file_name, true).'</pre>'.PHP_EOL);
echo('<pre>$merge_export_file_dir = '.print_r($merge_export_file_dir, true).'</pre>'.PHP_EOL);
if (!file_exists($merge_export_dir)){ die("Error: Merge path not found at &quot;{$merge_export_dir}&quot;."); }
else { echo('<pre style="color: lime;">Merge path exists at &quot;'.$merge_export_dir.'&quot;!</pre>'.PHP_EOL); }


// -- PARSE MANIFEST FILE -- //

// Load the manifest file and decode it into an array
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$merge_file_manifest_json = file_get_contents($merge_file_manifest_path);
$merge_file_manifest_array = json_decode($merge_file_manifest_json, true);
echo('<pre>$merge_file_manifest_path = '.print_r($merge_file_manifest_path, true).'</pre>'.PHP_EOL);
//echo('<pre>$merge_file_manifest_json = '.print_r($merge_file_manifest_json, true).'</pre>'.PHP_EOL);
echo('<pre>$merge_file_manifest_array = '.print_r($merge_file_manifest_array, true).'</pre>'.PHP_EOL);

// Loop through manifest files and expand into full paths, making sure they actually exist
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$manifest_files_in_order = array();
$manifest_files_last_mod = 0;
$manifest_files_total = 0;
$merge_file_manifest_items = !empty($merge_file_manifest_array['items']) ? $merge_file_manifest_array['items'] : array();
foreach ($merge_file_manifest_items as $key => $file){
    $file_path = $merge_base_dir.$merge_file_path.'/'.$file.'.pdf';
    if (!file_exists($file_path)){
        echo('<pre style="color: red;">Error: File not found at &quot;'.$file_path.'&quot;!</pre>'.PHP_EOL);
        unset($merge_file_manifest_items[$key]);
        continue;
    }
    $file_mod = filemtime($file_path);
    if ($file_mod > $manifest_files_last_mod){ $manifest_files_last_mod = $file_mod; }
    $manifest_files_in_order[$key] = $file_path;
    $manifest_files_total++;
}
echo('<pre class="array">$manifest_files_in_order = '.print_r($manifest_files_in_order, true).'</pre>'.PHP_EOL);

// Make sure we have files to merge or we have to abort prematurely
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
if (empty($manifest_files_in_order)){
    $expected = count($merge_file_manifest_items);
    $found = count($manifest_files_in_order);
    echo('<pre style="color: red;">Error: No files found to merge (expected: '.$expected.' / found: '.$found.')!</pre>'.PHP_EOL);
    killScript(__LINE__, "No files found to merge (expected: '.$expected.' / found: '.$found.')");
}


// -- MERGE PDFs + EXPORT TO FILE + ADD METADATA -- //

// If the merge file does not already-exist at this point, it means we need to (re)generate it now
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
if (file_exists($merge_export_file_dir)){
    // If the merge file already exists, check to see if it's too old and needs to be deleted
    echo('<pre>Checking for existing merged file....</pre>'.PHP_EOL);
    $merge_export_file_mod = filemtime($merge_export_file_dir);
    $merge_export_file_too_old = $merge_export_file_mod < $manifest_files_last_mod ? true : false;
    if ($overwrite_existing || $merge_export_file_too_old){ unlink($merge_export_file_dir); echo('<pre>Deleted existing file '.($overwrite_existing ? 'as requested' : 'because too old').'</pre>'.PHP_EOL); }
    if ($merge_export_file_too_old && file_exists($merge_export_file_dir)){ killScript(__LINE__, "Older merge file already exists but could not be deleted!"); }
}
if (!file_exists($merge_export_file_dir)){
    // If the merge file doesn't exist (or was just deleted), we can generate a new one now
    echo('<pre>Merging PDFs into a single file....</pre>'.PHP_EOL);
    ini_set('memory_limit', '512M'); // increase memory a lot
    ini_set('max_execution_time', 300); // and make the timeout longer too

    // Define the export paths for the various steps here
    $step1_meta_file_path = str_replace('.pdf', '.metadata.txt', $merge_export_file_dir);
    $step2_merged_file_path = str_replace('.pdf', '.merged.pdf', $merge_export_file_dir);
    $step3_update_file_path = str_replace('.pdf', '.merged-with-meta.pdf', $merge_export_file_dir);
    $step4_export_file_path = $merge_export_file_dir;
    echo('<pre>$step1_meta_file_path: '.PHP_EOL.print_r($step1_meta_file_path, true).'</pre>'.PHP_EOL);
    echo('<pre>$step2_merged_file_path: '.PHP_EOL.print_r($step2_merged_file_path, true).'</pre>'.PHP_EOL);
    echo('<pre>$step3_update_file_path: '.PHP_EOL.print_r($step3_update_file_path, true).'</pre>'.PHP_EOL);
    echo('<pre>$step4_export_file_path: '.PHP_EOL.print_r($step4_export_file_path, true).'</pre>'.PHP_EOL);

    // Create temp metadata for this file and then save to a file
    echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
    echo('<pre>Step 1: Pre-Generate Metadata for PDF</pre>'.PHP_EOL);
    $meta_data = array();
    $meta_data['title'] = !empty($merge_file_manifest_array['title']) ? $merge_file_manifest_array['title'] : 'Untitled';
    $meta_data['author'] = !empty($merge_file_manifest_array['author']) ? $merge_file_manifest_array['author'] : 'Unknown';
    $meta_data['subject'] = !empty($merge_file_manifest_array['subject']) ? $merge_file_manifest_array['subject'] : 'Mega Man RPG Prototype Community Thread';
    $meta_data['keywords'] = !empty($merge_file_manifest_array['keywords']) ? $merge_file_manifest_array['keywords'] : 'mega man, mega man rpg, mega man rpg prototype, community, thread, post, comment';
    $meta_data['published'] = !empty($merge_file_manifest_array['published']) ? $merge_file_manifest_array['published'] : time();
    $meta_data['updated'] = !empty($merge_file_manifest_array['updated']) ? $merge_file_manifest_array['updated'] : time();
    $meta_data_string = call_user_func(function($data){
        $lines = array();
        foreach ($data as $key => $value){ $lines[] = 'InfoBegin' .PHP_EOL. 'InfoKey: '.$key .PHP_EOL. 'InfoValue: '.$value; }
        return implode(PHP_EOL, $lines);
        }, $meta_data);
    echo('<pre>$meta_data: '.print_r($meta_data, true).'</pre>'.PHP_EOL);
    echo('<pre class="array">$meta_data_string: '.PHP_EOL.print_r($meta_data_string, true).'</pre>'.PHP_EOL);
    file_put_contents($step1_meta_file_path, trim($meta_data_string));
    // Check to ensure the metadata file was created successfully
    if (!file_exists($step1_meta_file_path)){ killScript(__LINE__, "Metadata file failed to create at &quot;{$step1_meta_file_path}&quot;."); }
    else { echo('<pre style="color: lime;">Metadata created successfully at &quot;'.$step1_meta_file_path.'&quot;!</pre>'.PHP_EOL); }

    // Use the installed PDFTK library to merge/combine files into a single PDF in manifest order
    echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
    echo('<pre>Step 2: Merge PDFs and Export to File</pre>'.PHP_EOL);
    $args = array();
    $args[] = 'pdftk';
    foreach ($manifest_files_in_order as $file_path){ $args[] = escapeshellarg($file_path); } // input files
    $args[] = 'cat output';
    $args[] = escapeshellarg($step2_merged_file_path); // output file
    $args = array_filter($args);
    $cmd = implode(' ', $args).' 2>&1';
    exec($cmd, $output, $result_code);
    echo('<pre>$cmd = '.print_r($cmd, true).'</pre>'.PHP_EOL);
    echo('<pre>$output = '.print_r($output, true).'</pre>'.PHP_EOL);
    echo('<pre>$result_code = '.print_r($result_code, true).'</pre>'.PHP_EOL);
    echo('<pre>...done!</pre>'.PHP_EOL);
    // Check to ensure the merged file was created successfully
    if (!file_exists($step2_merged_file_path)){ killScript(__LINE__, "Merge failed to create file at &quot;{$step2_merged_file_path}&quot;."); }
    else { echo('<pre style="color: lime;">Merge file created successfully at &quot;'.$step2_merged_file_path.'&quot;!</pre>'.PHP_EOL); }

    // Use the installed EXIFTOOL library to update the metadata in the merged file the advanced way
    echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
    echo('<pre>Step 3: Update PDF File with Metadata</pre>'.PHP_EOL);
    $args = array();
    $args[] = 'exiftool';
    $args[] = '-Title='.escapeshellarg($meta_data['title']);
    $args[] = '-Author='.escapeshellarg($meta_data['author']);
    $args[] = '-Subject='.escapeshellarg($meta_data['subject']);
    $args[] = '-Keywords='.escapeshellarg($meta_data['keywords']);
    $args[] = '-CreateDate='.escapeshellarg(date('Y:m:d H:i:s', $merge_file_manifest_array['published']));
    $args[] = '-ModifyDate='.escapeshellarg(date('Y:m:d H:i:s', $merge_file_manifest_array['updated']));
    $args[] = '-overwrite_original';
    $args[] = escapeshellarg($step2_merged_file_path); // input file
    $args[] = '-o';
    $args[] = escapeshellarg($step3_update_file_path); // output file
    $args = array_filter($args);
    $cmd = implode(' ', $args).' 2>&1';
    exec($cmd, $output, $result_code);
    echo('<pre>$cmd = '.print_r($cmd, true).'</pre>'.PHP_EOL);
    echo('<pre>$output = '.print_r($output, true).'</pre>'.PHP_EOL);
    echo('<pre>$result_code = '.print_r($result_code, true).'</pre>'.PHP_EOL);
    echo('<pre>...done!</pre>'.PHP_EOL);
    // Check to ensure the output file w/ metadata was created successfully
    if (!file_exists($step3_update_file_path)){ killScript(__LINE__, "Failed to update merged file at &quot;{$step3_update_file_path}&quot;."); }
    else { echo('<pre style="color: lime;">Merged file updated successfully at &quot;'.$step3_update_file_path.'&quot;!</pre>'.PHP_EOL); }

    // Everything seems to have worked out, so let's copy the final file to the export path
    echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
    echo('<pre>Step 4: Export Finalized Document & Cleanup Temp Files</pre>'.PHP_EOL);
    copy($step3_update_file_path, $step4_export_file_path);
    // Check to ensure the final export file was created successfully
    if (!file_exists($step4_export_file_path)){ killScript(__LINE__, "Merge failed to create file at &quot;{$step4_export_file_path}&quot;."); }
    else { echo('<pre style="color: lime;">Merge created successfully at &quot;'.$step4_export_file_path.'&quot;!</pre>'.PHP_EOL); }

    // Clean up the temp files
    echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
    echo('<pre>Cleaning up temp files</pre>'.PHP_EOL);
    if (file_exists($step1_meta_file_path)){ unlink($step1_meta_file_path); }
    if (file_exists($step2_merged_file_path)){ unlink($step2_merged_file_path); }
    if (file_exists($step3_update_file_path)){ unlink($step3_update_file_path); }

} else {
    echo('<pre style="color: lime;">Merged PDF file already exists!</pre>'.PHP_EOL);
}

// Return the output of the export process
$return_data = array();
$return_data['image'] = '';
$return_data['document'] = $merge_export_file_url;
$return_data['pages'] = $manifest_files_total;
exitScript($return_data);

// We are done
exit();

?>
