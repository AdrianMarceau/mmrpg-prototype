<?php

// THREAD TO PDF CONVERSION SCRIPT
// This script assumes and uses a page-width of 1040px or approx 780pt for the document

// Require the application top file
define('MMRPG_EXCLUDE_GAME_LOGIC', true);
require_once('../top.php');

// Start the output buffer to collect debug info
ob_start();

// Print out some debug styles to make writing this script a bit easier
echo('<pre style="color: magenta;">First test on line '.__LINE__.'!</pre>'.PHP_EOL);
echo('<style> pre { max-width: 100%; } pre:not(.array) { white-space: normal; } pre.array > data { display: block; max-height: 200px; overflow: auto; } </style>'.PHP_EOL);

/* -- COLLECT THREAD INFO -- */

// Define some helper functions for the export process
function exportKeyGen($src, $dst, $mod){
    return substr(md5(implode('##'.MMRPG_SETTINGS_EXPORTAUTH_SALT.'##', array($src, $dst, $mod))), 6, 6);
}

// Collect the thread ID from the URL path if available
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$thread_id = 0;
$thread_token = '';
$thread_updated = time();
$category_id = 0;
$category_token = '';
$base_thread_url = '';
if (!empty($_GET['thread'])
    && is_numeric($_GET['thread'])
    && $_GET['thread'] > 0){
    $thread_id = intval($_GET['thread']);
    $thread_info = cms_thread::get_thread_info($thread_id, true);
    echo('<pre>$thread_id = '.print_r($thread_id, true).'</pre>'.PHP_EOL);
    echo('<pre class="array">$thread_info = <data>'.print_r($thread_info, true).'</data></pre>'.PHP_EOL);
    if (empty($thread_info)){ die("Error: Thread not found for ID &quot;{$thread_id}&quot;."); }
    $thread_token = $thread_info['thread_token'];
    $thread_updated = !empty($thread_info['thread_mod_date']) ? $thread_info['thread_mod_date'] : $thread_info['thread_date'];
    $category_id = $thread_info['category_id'];
    $category_token = $thread_info['category_token'];
    if (empty($thread_token)){ die("Error: Thread token not found for ID &quot;{$thread_id}&quot;."); }
    if (empty($thread_info['thread_published'])){ die("Error: Thread not published for ID &quot;{$thread_id}&quot;."); }
    if (empty($category_id)){ die("Error: Thread category not found for ID &quot;{$thread_id}&quot;."); }
    if (empty($category_token)){ die("Error: Thread category not found for ID &quot;{$thread_id}&quot;."); }
    if ($thread_info['category_token'] == 'personal'){ die("Error: Thread category not allowed for ID &quot;{$thread_id}&quot;."); }
}
if (empty($thread_id)){ die("Error: Thread ID not found in URL path."); }
elseif (empty($thread_token)){ die("Error: Thread token not found for ID &quot;{$thread_id}&quot;."); }
else { echo('<pre style="color: lime;">Thread found w/ ID &quot;'.$thread_id.'&quot; and token &quot;'.$thread_token.'&quot;!</pre>'.PHP_EOL); }
$base_thread_url = 'community/'.$category_token.'/'.$thread_id.'/'.$thread_token.'/';
echo('<pre>$base_thread_url = '.print_r($base_thread_url, true).'</pre>'.PHP_EOL);
if (empty($base_thread_url)){ die("Error: Base thread URL could not be generated for thread ID &quot;{$thread_id}&quot;."); }

// Now that we have that information, let's build a manifest of all the thread/comment URLs we'll need
$src_base = $base_thread_url.'view=print&limit=1';
$dst_base = 'threads/'.$category_token.'/'.$thread_id.'/'.str_pad($thread_id, 6, '0', STR_PAD_LEFT); //.'_'.$thread_token;
$post_ids = !empty($thread_info['thread_post_ids']) ? explode(',', $thread_info['thread_post_ids']) : array();
$post_ids_count = count($post_ids);
$export_manifest = array();
$id_pad = function($id){ return str_pad($id, 6, '0', STR_PAD_LEFT); };
$export_manifest[] = array('src' => $src_base.'&offset=-1', 'dst' => $dst_base.'_'.$id_pad(0), 'name' => 'Thread-'.$thread_id, 'mod' => $thread_info['thread_date']);
foreach ($post_ids AS $key => $id){ $export_manifest[] = array('src' => $src_base.'&offset=0&range='.$id, 'dst' => $dst_base.'_'.$id_pad($id), 'name' => 'Comment-'.$id.'', 'mod' => $thread_info['thread_date']); }
echo('<pre>$src_base = '.print_r($src_base, true).'</pre>'.PHP_EOL);
echo('<pre>$dst_base = '.print_r($dst_base, true).'</pre>'.PHP_EOL);
echo('<pre class="array">$post_ids = <data>'.print_r($post_ids, true).'</data></pre>'.PHP_EOL);
echo('<pre class="array">$export_manifest = <data>'.print_r($export_manifest, true).'</data></pre>'.PHP_EOL);

/*
// Check to see if we can merge them now
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$file1 = 'mmrpg-roleplay-community_thread-6634_chaos-zone-part-iv_2025-03-23.pdf';
$file2 = 'mmrpg-roleplay-community_thread-6634_chaos-zone-part-iv_2025-03-22-18-11.pdf';
$file3 = 'mmrpg-roleplay-community_thread-6634_chaos-zone-part-iv_2025-03-22-17-42.pdf';
require_once($pdfmerger_script_path);
use PDFMerger\PDFMerger;
$pdf = new PDFMerger;
$pdf->addPDF($export_dir.$file1);
$pdf->addPDF($export_dir.$file2);
$pdf->addPDF($export_dir.$file3);
$pdf->merge('file', $export_dir.'mmrpg-merged_'.date('Y-m-d-H-i').'.pdf');
//$pdf->merge('download','merged.pdf');
*/

echo('<pre style="color: magenta;">Final test on line '.__LINE__.'!</pre>'.PHP_EOL);

// Collect output from the buffer and clear it
$debug_output = ob_get_clean();

// Print-out the debug output
//echo($debug_output);
//exit();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="<?= MMRPG_CONFIG_ROOTURL ?>">
    <title>Thread to PDF Conversion</title>
    <style> html, body { font-family: Arial, sans-serif; text-align: center; font-size: 13px; line-height: 1.6; color: #efefef; background-color: #262626; margin: 0; padding: 0; } body { padding: 20px; } </style>
    <style> a { color: #007cf4; text-decoration: underline; } a:hover { color: #3cc0fc; text-decoration: none; } i, b, u { font-style: normal; font-weight: normal; text-decoration: none; } </style>
    <meta name="darkreader-lock" content="already-dark-mode" />
</head>
<body>
    <div id="intro-text">
        <h1>Thread to PDF Conversion</h1>
        <p>This script exports the requested thread and all its comments into PDF format for download:</p>
        <ul>
            <li class="category"><strong>Category</strong>: <?= $category_token ?> [<?= $category_id ?>]</li>
            <li class="id"><strong>ID</strong>: <?= $thread_id ?></li>
            <li class="token"><strong>Token</strong>: <?= $thread_token ?></li>
            <li class="name"><strong>Name</strong>: &quot;<?= $thread_info['thread_name'] ?>&quot;</li>
            <li class="published"><strong>Published</strong>: <?= date('Y-m-d H:i:s', $thread_info['thread_date']) ?></li>
            <li class="updated"><strong>Updated</strong>: <?= date('Y-m-d H:i:s', $thread_updated) ?></li>
            <li class="comments"><strong>Comments</strong>: <?= $post_ids_count ?></li>
            <li class="url"><strong>URL</strong>: <a href="<?= $base_thread_url ?>" target="_blank"><?= $base_thread_url ?></a></li>
        </ul>
        <p>Click the "Generate" button to start generating the document (if not already generated).</p>
        <p>Please be patient while the script generates each section of the document to be assembled together.</p>
        <p>Once the script has finished (or if the document already exists), a "Download" button will appear allowing you to download the file.</p>
    </div>
    <div id="thread-to-pdf">
        <div id="buttons">
            <button id="start" class="button">Generate PDF</button>
            <button id="stop" class="button" disabled>Stop Generating</button>
            <button id="download" class="button" disabled>Download PDF</button>
            <i class="status"></i>
        </div>
        <div id="manifest">
        <?
        $manifest_base_dir = rtrim(dirname($dst_base), '/').'/';
        $manifest_file_path = $manifest_base_dir.'manifest.json';
        $manifest_file_list = array();
        $src_base_url = MMRPG_CONFIG_ROOTURL;
        $dst_base_dir = MMRPG_CONFIG_ROOTDIR.'.cache/';
        $dst_base_url = MMRPG_CONFIG_ROOTURL.'.cache/';
        if (!empty($export_manifest)){
            echo('<ol class="list">'.PHP_EOL);
            $count = count($export_manifest);
            foreach ($export_manifest AS $key => $item){
                if (empty($item['src']) || empty($item['dst'])){ continue; }
                $item_num = ($key + 1); //$item_num = preg_replace('/^([0]+)?/', '<b>$1</b>', str_pad(($key + 1), 3, '0', STR_PAD_LEFT));
                $name = (!empty($item['name']) ? $item['name'] : 'Item '.($key + 1));
                $src_path = (!empty($item['src']) ? $item['src'] : '');
                $dst_path = (!empty($item['dst']) ? $item['dst'] : '');
                $mod_time = (!empty($item['mod']) ? $item['mod'] : time());
                $auth_key = exportKeyGen($src_path, $dst_path, $mod_time);
                $src_href = $src_base_url.$src_path;
                $dst_href = $dst_base_url.$dst_path;
                $dst_img_type = 'png';
                $dst_img_path = $dst_path.'.'.$dst_img_type;
                $dst_img_href = $dst_base_url.$dst_img_path;
                $dst_img_exists = file_exists($dst_base_dir.$dst_img_path);
                $dst_doc_type = 'pdf';
                $dst_doc_path = $dst_path.'.'.$dst_doc_type;
                $dst_doc_href = $dst_base_url.$dst_doc_path;
                $dst_doc_exists = file_exists($dst_base_dir.$dst_doc_path);
                $manifest_file_list[] = basename($dst_path);
                $item_status = '';
                if ($dst_img_exists && $dst_doc_exists){ $item_status = 'complete'; }
                elseif ($dst_img_exists || $dst_doc_exists){ $item_status = 'pending'; }
                echo('<li class="item"'.
                    ' data-src="'.$src_path.'"'.
                    ' data-dst="'.$dst_path.'"'.
                    ' data-mod="'.$mod_time.'"'.
                    ' data-auth="'.$auth_key.'"'.
                    ' data-status="'.$item_status.'"'.
                    '>');
                    echo('<i class="bullet">&raquo;</i>');
                    echo('<strong class="num">'.$item_num.' <b>/ '.$count.'</b></strong>');
                    echo('<a class="name" href="'.$src_href.'" target="_blank">'.$name.'</a>');
                    if ($dst_img_exists){ echo('<a class="file img" href="'.$dst_img_href.'" target="_blank">'.strtoupper($dst_img_type).'</a>'); }
                    else { echo('<a class="file img" target="_blank"></a>'); }
                    if ($dst_doc_exists){ echo('<a class="file doc" href="'.$dst_doc_href.'" target="_blank">'.strtoupper($dst_doc_type).'</a>'); }
                    else { echo('<a class="file doc" target="_blank"></a>'); }
                    echo('<i class="status"></i>');
                echo('</li>'.PHP_EOL);
            }
            echo('</ol>'.PHP_EOL);
        }
        //error_log('$manifest_file_path: '.print_r($manifest_file_path, true));
        //error_log('$manifest_file_list: '.print_r($manifest_file_list, true));
        if (!is_dir($manifest_base_dir)){ recurseMakeDir($manifest_base_dir, $dst_base_dir); }
        if (file_exists($dst_base_dir.$manifest_file_path)){ unlink($dst_base_dir.$manifest_file_path); }
        $h = fopen($dst_base_dir.$manifest_file_path, 'w');
        fwrite($h, json_encode($manifest_file_list, JSON_PRETTY_PRINT));
        fclose($h);
        ?>
        </div>
    </div>
    <div id="debug-output">
        <strong class="title">Debug Output</strong>
        <div class="output hide-on-blur"><?= $debug_output ?></div>
    </div>
    <script src=".libs/jquery/jquery-1.6.1.min.js"></script>
    <script type="text/javascript">

        // -- Thread to PDF Conversion Script -- //
        (function(){

            // Define the manifest in json so we can access it later
            let exportManifest = <?= json_encode($export_manifest) ?>;

            // Define some paths for scripts we'll use later
            let baseExportHref = '<?= $dst_base_url ?>';
            let baseExportPath = 'scripts/export-to-pdf.php';
            let baseMergePath = 'scripts/merge-pdfs.php';

            // Pull in references to the main elements we'll be working with
            let $ = jQuery;
            let $context = $('#thread-to-pdf');
            let $manifest = $('#manifest', $context);
            let $buttons = $('#buttons', $context);
            let $start = $('#start', $context);
            let $stop = $('#stop', $context);
            let $download = $('#download', $context);

            // Wait until the document is ready before indexing data or delegating events
            $(document).ready(function(){

                // This is mostly for reference, but we can use it to track progress
                //console.log('Export Manifest:', exportManifest);

                // Attach the click event to the start & stop buttons
                let allowNextExport = false;
                let startExport = function(){
                    $stop.prop('disabled', false);
                    allowNextExport = true;
                    startNextExport();
                    };
                let stopExport = function(){
                    $stop.prop('disabled', true);
                    allowNextExport = false;
                    };
                $start.bind('click', function(event){
                    event.preventDefault();
                    //console.log('Start button clicked!');
                    startExport();
                    });
                $stop.bind('click', function(event){
                    event.preventDefault();
                    //console.log('Stop button clicked!');
                    stopExport();
                    });

                // Define a function to start the next export in the manifest
                let startNextExport = function(){

                    // Loop through each item in the manifest and update the status
                    if (!allowNextExport){ stopExport(); return false; }
                    $itemsToDo = $manifest.find('.item[data-status=""]');
                    if (!$itemsToDo.length){ stopExport(); return false; }
                    $itemsToDo.each(function(index, element){
                        //console.log('Searching for next export item...');
                        let $item = $(element);
                        let itemStatus = $item.attr('data-status');
                        //console.log('Item:', $item, 'Status:', itemStatus);
                        if (itemStatus === 'complete'){ return true; }
                        else { exportItem($item); }
                        return false;
                        });

                    };

                // Define a quick function for encoding a string for a URL parameter
                let urlEncode = function(str){ return encodeURIComponent(str).replace(/%20/g, '+'); };

                // Define a trigger function for the item export functionality
                let exportItem = function($item){
                    //console.log('Exporting item:', $item);
                    if (!$item.length){ return false; }
                    let itemStatus = $item.attr('data-status');
                    if (itemStatus === 'complete'){ return false; }
                    $item.attr('data-status', 'pending');
                    let itemNum = $item.find('.num').text();
                    let itemName = $item.find('.name').text();
                    let srcPath = $item.attr('data-src');
                    let dstPath = $item.attr('data-dst');
                    let modTime = $item.attr('data-mod');
                    let authCode = $item.attr('data-auth');
                    let exportPath = baseExportPath + '?' + [
                        'src='+urlEncode(srcPath),
                        'dst='+urlEncode(dstPath),
                        'mod='+urlEncode(modTime),
                        'auth='+urlEncode(authCode),
                        'debug=false',
                        'return=json'
                        ].join('&');
                    //console.log('exportPath:', exportPath);
                    let $imgFile = $item.find('.file.img');
                    let $docFile = $item.find('.file.doc');
                    let $status = $item.find('.status');
                    //console.log('Item:', itemNum, itemName, srcPath, dstPath, exportPath, itemStatus, '\n' + 'Status:', $status, $imgFile, $docFile);
                    if (!srcPath || !dstPath || !exportPath){ return false; }
                    $.ajax({
                        url: exportPath,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response){
                            //console.log('Export success:', response);
                            $item.attr('data-status', 'complete');
                            $status.css('background-color', 'lime');
                            if ($imgFile.length){ $imgFile.attr('href', baseExportHref+dstPath+'.png').html('PNG'); }
                            if ($docFile.length){ $docFile.attr('href', baseExportHref+dstPath+'.pdf').html('PDF'); }
                            startNextExport();
                            },
                        error: function(response){
                            //console.log('Export error:', response);
                            let responseData = response.responseText ? JSON.parse(response.responseText) : null;
                            //console.log('Export error data:', responseData);
                            $item.attr('data-status', 'error');
                            $status.css('background-color', 'red');
                            let msg = 'An error occurred while exporting item '+itemNum+' ('+itemName+').';
                            if (responseData && responseData.message){ msg += '\n' + 'Error: ' + responseData.message; }
                            msg += '\n' + 'Would you like to continue to the next?';
                            if (confirm(msg)){ startNextExport(); }
                            else { stopExport(); }
                            }
                        });
                    $item.attr('data-status', 'started');
                    };

               });

        })();

    </script>
    <style>

        /* General styles */

        #intro-text,
        #thread-to-pdf,
        #debug-output {
            display: block;
            box-sizing: border-box;
            width: 100%;
            max-width: 1024px;
            overflow: auto;
            border: 1px solid #464646;
            padding: 10px;
            margin: 0 auto 20px;
            border-radius: 6px;
            text-align: left;
            font-size: 13px;
            line-height: 1.6;
        }

        #intro-text:after,
        #intro-text ul:after,
        #thread-to-pdf:after,
        #thread-to-pdf #manifest:after,
        #thread-to-pdf #manifest .list:after,
        #thread-to-pdf #manifest .item:after,
        #thread-to-pdf #buttons:after,
        #debug-output:after {
            content: "";
            display: block;
            clear: both;
        }

        /* Intro text styles */

        #intro-text {
            background-color: #1e1e1e;
            padding: 12px 24px;
        }
        #intro-text ul {
            padding: 0;
            margin: 0 auto;
            list-style-type: none;
            font-family: monospace;
        }
        #intro-text li {
            display: block;
            float: left;
            margin: 0 5px 5px 0;
            padding: 0 5px 5px 0;
            width: auto;
        }
        #intro-text li.published,
        #intro-text li.url {
            clear: left;
        }

        /* Thread-to-pdf container */

        #thread-to-pdf {
            background-color: #262626;
            padding: 6px;
        }
        #thread-to-pdf > * {
            box-sizing: border-box;
        }

        /* Manifest styles */

        #thread-to-pdf #manifest {
            display: block;
            max-width: 100%;
            overflow: auto;
            padding: 0;
            margin: 0;
        }
        #thread-to-pdf #manifest .list {
            color: #fff;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        #thread-to-pdf #manifest .item {
            display: block;
            float: left;
            clear: none;
            width: calc((100% / 3) - 16px);
            /* min-width: 250px;  */
            min-width: 310px;
            max-width: 100%;
            overflow: auto;
            margin: 0 2px 4px;
            padding: 3px 6px 3px;
            /* background-color: #1e1e1e; */
            background-color: transparent;
            transition: background-color 0.3s;
            line-height: 1.6;
        }
        @media (max-width: 1024px) {
            #thread-to-pdf #manifest .item {
                width: calc((100% / 2) - 16px);
            }
        }
        @media (max-width: 800px) {
            #thread-to-pdf #manifest .item {
                width: auto;
                float: none;
                margin: 0 auto 4px;
            }
        }
        #thread-to-pdf #manifest .item:hover {
            background-color: #525252;
        }

        #thread-to-pdf #manifest .item .num,
        #thread-to-pdf #manifest .item .bullet,
        #thread-to-pdf #manifest .item .name,
        #thread-to-pdf #manifest .item .status,
        #thread-to-pdf #manifest .item .file {
            display: inline-block;
            box-sizing: border-box;
            vertical-align: middle;
            text-align: center;
            padding: 3px 6px;
            min-width: 1.6rem;
            min-height: 2rem;
            margin: 0 6px 0 0;
            border-radius: 3px;
        }

        #thread-to-pdf #manifest .item .num,
        #thread-to-pdf #manifest .item .name {
            /*  background-color: #1e1e1e; */
            /* background-color: #242424; */
            background-color: #1e1e1e;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.2);
            border-width: 1px 0 0 1px;
            box-shadow: inset -1px -1px 0 rgba(255, 255, 255, 0.05);
        }
        #thread-to-pdf #manifest .item:hover .status,
        #thread-to-pdf #manifest .item:hover .file {
            box-shadow: 1px 1px 0 #1e1e1e;
        }

        #thread-to-pdf #manifest .item .num {
            width: 70px;
        }
        #thread-to-pdf #manifest .item .num > b {
            opacity: 0.5;
        }
        #thread-to-pdf #manifest .item .bullet {
            padding: 3px 0;
            min-width: 0;
            width: 12px;
        }
        #thread-to-pdf #manifest .item .name {
            min-width: calc(100% - 192px);
        }

        #thread-to-pdf #manifest .item .file {
            padding: 1px;
            min-width: 2rem;
            /* background-color: #696969;  */
            background-color: #1e1e1e;
            text-decoration: none;
            color: #ccc;
            font-size: 11px;
            line-height: 2;
        }
        #thread-to-pdf #manifest .item .file[href] {
            background-color: #898989;
            color: #fff;
        }
        #thread-to-pdf #manifest .item .file[href].img { background-color: #007cf4; }
        #thread-to-pdf #manifest .item .file[href].img:hover { background-color: #3cc0fc; }
        #thread-to-pdf #manifest .item .file[href].doc { background-color: #ae4fc8; }
        #thread-to-pdf #manifest .item .file[href].doc:hover { background-color: #d77afc; }

        #thread-to-pdf #manifest .item .status {
            padding: 0;
            margin-top: 2px;
            min-width: 1.6rem;
            min-height: 1.6rem;
            background-color: #696969;
            border-radius: 50%;
            float: right;
            transform: scale(1.0);
        }
        @keyframes item-status-pulse {
            0% { transform: scale(1.0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1.0); }
        }
        #thread-to-pdf #manifest .item[data-status="pending"] .status { background-color: yellow; }
        #thread-to-pdf #manifest .item[data-status="started"] .status { background-color: orange; animation: item-status-pulse 1s infinite; }
        #thread-to-pdf #manifest .item[data-status="complete"] .status { background-color: lime; }
        #thread-to-pdf #manifest .item[data-status="error"] .status { background-color: red; }

        /* Buttons section */

        #thread-to-pdf #buttons {
            display: block;
            max-width: 100%;
            overflow: auto;
            border-bottom: 1px dotted #464646;
            padding: 9px 0 12px;
            margin: 0 6px 12px;
            position: relative;
            padding-right: 42px;
        }
        #thread-to-pdf #buttons .button {
            display: inline-block;
            width: 150px;
            overflow: auto;
            padding: 10px;
            margin: 0 6px 0 0;
            background-color: #007cf4;
            border: 1px solid #007cf4;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 3px;
            cursor: pointer;
            filter: brightness(1) saturate(1);
            transition: background-color 0.3s, border-color 0.3s, filter 0.3s;
        }
        #thread-to-pdf #buttons .button:hover {
            filter: brightness(1.1) saturate(1.1);
        }
        #thread-to-pdf #buttons .button#start {
            background-color: #007cf4;
            border-color: #007cf4;
        }
        #thread-to-pdf #buttons .button#stop {
            background-color: #ff0000;
            border-color: #ff0000;
        }
        #thread-to-pdf #buttons .button#download {
            background-color: #008001;
            border-color: #008001;
            float: right;
        }
        #thread-to-pdf #buttons .button[disabled] {
            background-color: #696969;
            border-color: #696969;
            color: #ccc;
            cursor: not-allowed;
            filter: brightness(1) saturate(0);
        }
        #thread-to-pdf #buttons .status {
            display: block;
            position: absolute;
            top: 10px;
            right: 5px;
            padding: 0;
            margin-top: 2px;
            width: 30px;
            height: 30px;
            background-color: #696969;
            border-radius: 50%;
            transform: scale(1.0);
        }
        @keyframes export-status-pulse {
            0% { transform: scale(1.0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1.0); }
        }

        /* Debug styles */

        #debug-output {
            display: block;
            overflow: auto;
            border-style: dotted;
            padding: 0;
            margin: 10px auto 0;
            text-align: left;
            font-size: 13px;
            line-height: 1.6;
        }
        #debug-output pre:not(.array) {
            white-space: normal;
        }
        #debug-output > .title {
            display: block;
            background-color: #1e1e1e;
            padding: 9px 12px;
        }
        #debug-output > .output {
            padding: 10px;
            font-family: monospace;
        }
        #debug-output:not(:hover) .hide-on-blur {
            display: none;
        }

    </style>
</body>
</html>
